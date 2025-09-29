<?php

namespace App\Services\Escrow;

use App\Enums\EscrowStatusEnum;
use App\Enums\EscrowTransactionTypeEnum;
use App\Models\Escrow;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Compliance\ComplianceReporter;
use App\Services\Escrow\Gateways\EscrowGateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EscrowService
{
    public function __construct(
        private readonly EscrowGateway $gateway,
        private readonly EscrowLedgerService $ledger,
        private readonly ComplianceReporter $compliance,
    ) {
    }

    public function initialize(ServiceRequest $job, User $consumer, User $provider, float $amount, string $currency, array $metadata = []): Escrow
    {
        return Escrow::query()->updateOrCreate([
            'service_request_id' => $job->getKey(),
            'consumer_id' => $consumer->getKey(),
            'provider_id' => $provider->getKey(),
        ], [
            'amount' => round($amount, 2),
            'currency' => strtoupper($currency),
            'status' => EscrowStatusEnum::AWAITING_FUNDING,
            'metadata' => $metadata,
            'expires_at' => Carbon::now()->addHours(config('escrow.release_sla_hours', 72)),
        ]);
    }

    public function fund(Escrow $escrow, float $amount, ?User $actor = null, array $metadata = []): Escrow
    {
        if ($amount <= 0) {
            throw new RuntimeException('Escrow amount must be positive.');
        }

        return DB::transaction(function () use ($escrow, $amount, $actor, $metadata) {
            /** @var Escrow $escrow */
            $escrow = Escrow::query()->lockForUpdate()->with('consumer')->find($escrow->getKey());
            if (!$escrow) {
                throw new RuntimeException('Escrow record missing.');
            }

            if (in_array($escrow->status, EscrowStatusEnum::TERMINAL_STATES, true)) {
                throw new RuntimeException('Escrow is already closed.');
            }

            $gatewayResponse = $this->gateway->createHold($escrow, $amount, $escrow->currency, $metadata);

            $escrow->fill([
                'status' => EscrowStatusEnum::FUNDED,
                'hold_reference' => $gatewayResponse['reference'],
                'funded_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addHours(config('escrow.release_sla_hours', 72)),
                'metadata' => array_merge($escrow->metadata ?? [], ['fund' => Arr::except($gatewayResponse, 'raw')]),
            ])->save();

            $this->ledger->record(
                $escrow,
                EscrowTransactionTypeEnum::HOLD,
                $amount,
                'credit',
                $actor,
                ['gateway_status' => $gatewayResponse['status']],
                $gatewayResponse['reference'],
                $gatewayResponse['raw']['charges']['data'][0]['id'] ?? null,
                'Escrow funded'
            );

            $this->compliance->report('escrow.funded', $escrow, $actor, [
                'amount' => $amount,
                'currency' => $escrow->currency,
                'job' => $escrow->service_request_id,
            ]);

            return $escrow->fresh(['transactions']);
        });
    }

    public function release(Escrow $escrow, float $amount, ?User $actor = null, array $metadata = []): Escrow
    {
        if ($amount <= 0) {
            throw new RuntimeException('Release amount must be positive.');
        }

        return DB::transaction(function () use ($escrow, $amount, $actor, $metadata) {
            /** @var Escrow $escrow */
            $escrow = Escrow::query()->lockForUpdate()->with('transactions')->find($escrow->getKey());
            if (!$escrow) {
                throw new RuntimeException('Escrow record missing.');
            }

            if ($escrow->available_amount + 0.01 < $amount) {
                throw new RuntimeException('Insufficient escrow balance to release.');
            }

            $gatewayResponse = $this->gateway->capture($escrow, $amount, $metadata);

            $this->ledger->record(
                $escrow,
                EscrowTransactionTypeEnum::RELEASE,
                $amount,
                'debit',
                $actor,
                ['gateway_status' => $gatewayResponse['status']],
                $gatewayResponse['reference'],
                $gatewayResponse['raw']['charges']['data'][0]['id'] ?? null,
                'Escrow release'
            );

            $escrow->refresh();
            $escrow->released_at = Carbon::now();
            $escrow->status = $escrow->available_amount <= 0.0
                ? EscrowStatusEnum::RELEASED
                : EscrowStatusEnum::PARTIALLY_RELEASED;
            if ($escrow->status === EscrowStatusEnum::RELEASED) {
                $escrow->expires_at = null;
            }
            $escrow->metadata = array_merge($escrow->metadata ?? [], ['release' => Arr::except($gatewayResponse, 'raw')]);
            $escrow->save();

            $this->compliance->report('escrow.released', $escrow, $actor, [
                'amount' => $amount,
                'remaining' => $escrow->available_amount,
            ]);

            return $escrow->fresh(['transactions']);
        });
    }

    public function refund(Escrow $escrow, float $amount, ?User $actor = null, array $metadata = []): Escrow
    {
        if ($amount <= 0) {
            throw new RuntimeException('Refund amount must be positive.');
        }

        return DB::transaction(function () use ($escrow, $amount, $actor, $metadata) {
            /** @var Escrow $escrow */
            $escrow = Escrow::query()->lockForUpdate()->with('transactions')->find($escrow->getKey());
            if (!$escrow) {
                throw new RuntimeException('Escrow record missing.');
            }

            if ($escrow->available_amount + 0.01 < $amount) {
                throw new RuntimeException('Insufficient escrow balance to refund.');
            }

            $gatewayResponse = $this->gateway->refund($escrow, $amount, $metadata);

            $this->ledger->record(
                $escrow,
                EscrowTransactionTypeEnum::REFUND,
                $amount,
                'debit',
                $actor,
                ['gateway_status' => $gatewayResponse['status']],
                $gatewayResponse['reference'],
                $gatewayResponse['raw']['id'] ?? null,
                'Escrow refund'
            );

            $escrow->refresh();
            $escrow->refunded_at = Carbon::now();
            $escrow->status = $escrow->available_amount <= 0.0
                ? EscrowStatusEnum::REFUNDED
                : EscrowStatusEnum::PARTIALLY_RELEASED;
            $escrow->metadata = array_merge($escrow->metadata ?? [], ['refund' => Arr::except($gatewayResponse, 'raw')]);
            $escrow->save();

            $this->compliance->report('escrow.refunded', $escrow, $actor, [
                'amount' => $amount,
                'remaining' => $escrow->available_amount,
            ]);

            return $escrow->fresh(['transactions']);
        });
    }

    public function cancel(Escrow $escrow, ?User $actor = null, array $metadata = []): Escrow
    {
        return DB::transaction(function () use ($escrow, $actor, $metadata) {
            /** @var Escrow $escrow */
            $escrow = Escrow::query()->lockForUpdate()->find($escrow->getKey());
            if (!$escrow) {
                throw new RuntimeException('Escrow record missing.');
            }

            if (in_array($escrow->status, EscrowStatusEnum::TERMINAL_STATES, true)) {
                return $escrow;
            }

            $gatewayResponse = $this->gateway->cancel($escrow, $metadata);

            $escrow->forceFill([
                'status' => EscrowStatusEnum::CANCELLED,
                'cancelled_at' => Carbon::now(),
                'metadata' => array_merge($escrow->metadata ?? [], ['cancel' => Arr::except($gatewayResponse, 'raw')]),
            ])->save();

            $this->compliance->report('escrow.cancelled', $escrow, $actor, []);

            return $escrow->fresh();
        });
    }

    public function enforceSlas(): void
    {
        $now = Carbon::now();
        Escrow::query()
            ->where('status', EscrowStatusEnum::FUNDED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->chunkById(50, function ($escrows) use ($now) {
                foreach ($escrows as $escrow) {
                    $escrow->markExpiredIfNecessary($now);
                    $escrow->save();

                    $this->compliance->report('escrow.sla_flagged', $escrow, null, [
                        'expires_at' => $escrow->expires_at,
                    ]);
                }
            });
    }
}
