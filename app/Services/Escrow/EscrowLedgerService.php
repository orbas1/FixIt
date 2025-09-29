<?php

namespace App\Services\Escrow;

use App\Enums\EscrowTransactionTypeEnum;
use App\Models\Escrow;
use App\Models\EscrowTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EscrowLedgerService
{
    public function record(
        Escrow $escrow,
        string $type,
        float $amount,
        string $direction,
        ?User $actor = null,
        array $metadata = [],
        ?string $reference = null,
        ?string $gatewayId = null,
        ?string $notes = null,
        ?Carbon $occurredAt = null,
    ): EscrowTransaction {
        $occurredAt ??= Carbon::now();

        return DB::transaction(function () use ($escrow, $type, $amount, $direction, $actor, $metadata, $reference, $gatewayId, $notes, $occurredAt) {
            $transaction = $escrow->transactions()->create([
                'type' => $type,
                'amount' => round($amount, 2),
                'direction' => $direction,
                'currency' => $escrow->currency,
                'reference' => $reference,
                'gateway_id' => $gatewayId,
                'actor_id' => $actor?->getKey(),
                'notes' => $notes,
                'metadata' => $metadata,
                'occurred_at' => $occurredAt,
            ]);

            $escrow->unsetRelation('transactions');
            $escrow->load('transactions');
            $this->syncEscrowTotals($escrow);

            return $transaction;
        });
    }

    public function syncEscrowTotals(Escrow $escrow): void
    {
        $released = $escrow->transactions
            ->where('type', EscrowTransactionTypeEnum::RELEASE)
            ->sum('amount');

        $refunded = $escrow->transactions
            ->where('type', EscrowTransactionTypeEnum::REFUND)
            ->sum('amount');

        $escrow->forceFill([
            'amount_released' => round($released, 2),
            'amount_refunded' => round($refunded, 2),
        ])->save();
    }
}
