<?php

namespace App\Services\DataGovernance;

use App\Models\DataAsset;
use App\Models\DpiaFinding;
use App\Models\DpiaRecord;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class DpiaAutomationService
{
    public function __construct(
        private readonly DataGovernanceRegistry $registry,
        private readonly Dispatcher $events
    ) {
    }

    public function ensureCoverage(DataAsset $asset): DpiaRecord
    {
        $activeRecord = $asset->dpiaRecords()
            ->whereNotIn('status', ['approved', 'rejected'])
            ->latest('created_at')
            ->first();

        if ($activeRecord) {
            return $activeRecord;
        }

        $risk = $this->calculateRiskScore($asset);
        $status = $risk >= config('data_governance.dpia.risk_threshold') ? 'in_review' : 'draft';

        /** @var DpiaRecord $record */
        $record = $asset->dpiaRecords()->create([
            'status' => $status,
            'risk_level' => $this->mapRiskLevel($risk),
            'risk_score' => $risk,
            'assessment_summary' => sprintf(
                'Automated DPIA initiated for %s with calculated risk score %d.',
                $asset->name,
                $risk
            ),
            'mitigation_actions' => [],
            'residual_risks' => [],
            'submitted_at' => $status === 'draft' ? null : now(),
            'next_review_at' => now()->addMonths(config('data_governance.dpia.default_review_months')),
        ]);

        if ($risk >= config('data_governance.dpia.risk_threshold')) {
            $this->createDefaultFindings($record, $asset);
        }

        $this->events->dispatch('dpia.record.created', [$record]);

        return $record;
    }

    public function reconcileMitigations(DpiaRecord $record, array $mitigations): DpiaRecord
    {
        $record->mitigation_actions = $mitigations;
        $record->save();

        return $record->refresh();
    }

    public function closeRecord(DpiaRecord $record, ?int $reviewerId): DpiaRecord
    {
        $record->status = 'approved';
        $record->reviewed_by = $reviewerId;
        $record->approved_at = now();
        $record->next_review_at = Carbon::parse($record->next_review_at ?? now())->addMonths(
            config('data_governance.dpia.default_review_months')
        );
        $record->save();

        return $record->refresh();
    }

    private function mapRiskLevel(int $risk): string
    {
        return match (true) {
            $risk >= 75 => 'high',
            $risk >= 50 => 'medium',
            default => 'low',
        };
    }

    private function createDefaultFindings(DpiaRecord $record, DataAsset $asset): void
    {
        $controls = Arr::wrap($asset->monitoring_controls);
        $encryption = collect($asset->residencyPolicies)
            ->pluck('encryption_profile')
            ->unique()
            ->values()
            ->all();

        $missingEncryption = empty($encryption);
        $missingMonitoring = empty($controls);

        if ($missingEncryption) {
            $this->createFinding($record, [
                'category' => 'encryption',
                'severity' => 'high',
                'finding' => 'No encryption profile configured for residency policies.',
                'recommendation' => 'Define encryption controls aligned to approved profiles.',
            ]);
        }

        if ($missingMonitoring) {
            $this->createFinding($record, [
                'category' => 'monitoring',
                'severity' => 'medium',
                'finding' => 'Monitoring controls missing for asset telemetry.',
                'recommendation' => 'Implement centralized logging and anomaly detection.',
            ]);
        }
    }

    private function createFinding(DpiaRecord $record, array $attributes): DpiaFinding
    {
        return $record->findings()->create(array_merge($attributes, [
            'status' => Arr::get($attributes, 'status', 'open'),
            'due_at' => now()->addWeeks(4),
        ]));
    }

    private function calculateRiskScore(DataAsset $asset): int
    {
        $classificationWeight = match ($asset->classification) {
            'restricted' => 35,
            'confidential' => 25,
            'internal' => 10,
            default => 5,
        };

        $elementWeight = min(count(Arr::wrap($asset->data_elements)) * 5, 25);
        $retentionWeight = $asset->retention_period_days ? min((int) ($asset->retention_period_days / 30), 25) : 0;
        $crossBorderWeight = $asset->residencyPolicies()
            ->where('cross_border_allowed', true)
            ->count() * 5;

        $riskScore = $classificationWeight + $elementWeight + $retentionWeight + $crossBorderWeight;

        if ($asset->requires_dpia) {
            $riskScore += 10;
        }

        return min($riskScore, 100);
    }
}
