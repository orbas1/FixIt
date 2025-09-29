<?php

namespace App\Services\Dispute;

use App\Enums\DisputeMessageVisibilityEnum;
use App\Enums\DisputeStageEnum;
use App\Enums\DisputeStatusEnum;
use App\Models\Dispute;
use App\Models\DisputeEvent;
use App\Models\DisputeMessage;
use App\Models\Escrow;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Compliance\ComplianceReporter;
use App\Services\Escrow\EscrowService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DisputeService
{
    public function __construct(
        private readonly EscrowService $escrowService,
        private readonly ComplianceReporter $compliance,
    ) {
    }

    public function open(
        ServiceRequest $job,
        User $openedBy,
        User $against,
        string $reasonCode,
        ?Escrow $escrow = null,
        array $context = []
    ): Dispute {
        return DB::transaction(function () use ($job, $openedBy, $against, $reasonCode, $escrow, $context) {
            $dispute = Dispute::create([
                'service_request_id' => $job->getKey(),
                'escrow_id' => $escrow?->getKey(),
                'opened_by_id' => $openedBy->getKey(),
                'against_id' => $against->getKey(),
                'stage' => DisputeStageEnum::EVIDENCE,
                'status' => DisputeStatusEnum::OPEN,
                'reason_code' => $reasonCode,
                'summary' => $context['summary'] ?? null,
                'deadline_at' => $this->deadlineForStage(DisputeStageEnum::EVIDENCE),
                'sla_timer_started_at' => Carbon::now(),
                'metadata' => Arr::except($context, ['summary']),
            ]);

            $this->recordEvent($dispute, $openedBy, null, DisputeStageEnum::EVIDENCE, 'dispute.opened', [
                'reason' => $reasonCode,
            ]);

            $this->compliance->report('dispute.opened', $dispute, $openedBy, [
                'job' => $job->getKey(),
                'reason' => $reasonCode,
            ]);

            return $dispute->fresh(['messages', 'events']);
        });
    }

    public function postMessage(Dispute $dispute, User $author, string $body, string $visibility = DisputeMessageVisibilityEnum::PARTIES, array $attachments = []): DisputeMessage
    {
        if (!in_array($visibility, [DisputeMessageVisibilityEnum::PARTIES, DisputeMessageVisibilityEnum::ADMIN_ONLY], true)) {
            throw new RuntimeException('Invalid dispute message visibility.');
        }

        $message = $dispute->messages()->create([
            'author_id' => $author->getKey(),
            'body' => $body,
            'visibility' => $visibility,
            'attachments' => $attachments,
        ]);

        $this->compliance->report('dispute.message', $dispute, $author, [
            'visibility' => $visibility,
        ]);

        return $message;
    }

    public function advanceStage(Dispute $dispute, string $targetStage, User $actor, ?string $note = null, array $metadata = []): Dispute
    {
        if (!in_array($targetStage, $this->stageSequence(), true)) {
            throw new RuntimeException('Unsupported dispute stage transition.');
        }

        return DB::transaction(function () use ($dispute, $targetStage, $actor, $note, $metadata) {
            /** @var Dispute $dispute */
            $dispute = Dispute::query()->lockForUpdate()->find($dispute->getKey());
            if (!$dispute) {
                throw new RuntimeException('Dispute not found.');
            }

            $fromStage = $dispute->stage;
            $dispute->stage = $targetStage;
            $dispute->status = $this->statusForStage($targetStage);
            $dispute->deadline_at = $this->deadlineForStage($targetStage);

            if (in_array($targetStage, [DisputeStageEnum::RESOLVED, DisputeStageEnum::CANCELLED], true)) {
                $dispute->closed_at = Carbon::now();
            }

            $dispute->metadata = array_merge($dispute->metadata ?? [], $metadata);
            $dispute->save();

            $this->recordEvent($dispute, $actor, $fromStage, $targetStage, 'dispute.stage_changed', [
                'note' => $note,
            ]);

            $this->compliance->report('dispute.stage_changed', $dispute, $actor, [
                'from' => $fromStage,
                'to' => $targetStage,
            ]);

            return $dispute->fresh(['events']);
        });
    }

    public function settle(
        Dispute $dispute,
        User $actor,
        float $releaseAmount,
        float $refundAmount,
        array $resolutionDetails = []
    ): Dispute {
        if ($releaseAmount < 0 || $refundAmount < 0) {
            throw new RuntimeException('Settlement amounts must be positive.');
        }

        return DB::transaction(function () use ($dispute, $actor, $releaseAmount, $refundAmount, $resolutionDetails) {
            /** @var Dispute $locked */
            $locked = Dispute::query()->lockForUpdate()->with('escrow')->find($dispute->getKey());
            if (!$locked) {
                throw new RuntimeException('Dispute not found.');
            }

            $escrow = $locked->escrow;
            if ($escrow) {
                if ($releaseAmount > 0) {
                    $this->escrowService->release($escrow, $releaseAmount, $actor, ['reason' => 'dispute_settlement']);
                }

                if ($refundAmount > 0) {
                    $this->escrowService->refund($escrow, $refundAmount, $actor, ['reason' => 'dispute_settlement']);
                }
            }

            $previousStage = $locked->stage;

            $locked->forceFill([
                'stage' => DisputeStageEnum::RESOLVED,
                'status' => DisputeStatusEnum::RESOLVED,
                'closed_at' => Carbon::now(),
                'deadline_at' => null,
                'resolution' => array_merge($locked->resolution ?? [], [
                    'release' => $releaseAmount,
                    'refund' => $refundAmount,
                    'details' => $resolutionDetails,
                ]),
            ])->save();

            $this->recordEvent($locked, $actor, $previousStage, DisputeStageEnum::RESOLVED, 'dispute.settled', [
                'release' => $releaseAmount,
                'refund' => $refundAmount,
            ]);

            $this->compliance->report('dispute.settled', $locked, $actor, [
                'release' => $releaseAmount,
                'refund' => $refundAmount,
            ]);

            return $locked->fresh(['events']);
        });
    }

    public function autoAdvanceExpired(): void
    {
        $now = Carbon::now();
        Dispute::query()
            ->with(['openedBy', 'against'])
            ->whereNull('closed_at')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->chunkById(50, function ($disputes) {
                foreach ($disputes as $dispute) {
                    $next = $this->nextStage($dispute->stage);
                    if ($next === $dispute->stage) {
                        continue;
                    }

                    $actor = $dispute->openedBy;
                    $dispute = $this->advanceStage($dispute, $next, $actor ?? $dispute->against, 'Auto-advanced due to SLA breach');

                    $this->compliance->report('dispute.sla_auto_advance', $dispute, $actor, [
                        'previous_stage' => $dispute->stage,
                        'deadline_at' => $dispute->deadline_at,
                    ]);
                }
            });
    }

    protected function deadlineForStage(string $stage): ?Carbon
    {
        $hours = config("disputes.sla_hours.$stage");
        if ($hours === null) {
            return null;
        }

        return Carbon::now()->addHours((int) $hours);
    }

    protected function statusForStage(string $stage): string
    {
        return match ($stage) {
            DisputeStageEnum::EVIDENCE => DisputeStatusEnum::NEEDS_ACTION_PROVIDER,
            DisputeStageEnum::MEDIATION => DisputeStatusEnum::UNDER_REVIEW,
            DisputeStageEnum::ARBITRATION => DisputeStatusEnum::UNDER_REVIEW,
            DisputeStageEnum::RESOLVED => DisputeStatusEnum::RESOLVED,
            DisputeStageEnum::CANCELLED => DisputeStatusEnum::CANCELLED,
            default => DisputeStatusEnum::OPEN,
        };
    }

    protected function recordEvent(Dispute $dispute, ?User $actor, ?string $fromStage, ?string $toStage, string $action, array $meta = []): DisputeEvent
    {
        return $dispute->events()->create([
            'actor_id' => $actor?->getKey(),
            'from_stage' => $fromStage,
            'to_stage' => $toStage,
            'action' => $action,
            'notes' => $meta['note'] ?? null,
            'metadata' => Arr::except($meta, ['note']),
            'created_at' => Carbon::now(),
        ]);
    }

    protected function nextStage(string $current): string
    {
        $sequence = $this->stageSequence();
        $index = array_search($current, $sequence, true);

        if ($index === false || $index === count($sequence) - 1) {
            return $current;
        }

        return $sequence[$index + 1];
    }

    protected function stageSequence(): array
    {
        return [
            DisputeStageEnum::EVIDENCE,
            DisputeStageEnum::MEDIATION,
            DisputeStageEnum::ARBITRATION,
            DisputeStageEnum::RESOLVED,
        ];
    }
}
