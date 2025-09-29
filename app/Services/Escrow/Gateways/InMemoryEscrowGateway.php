<?php

namespace App\Services\Escrow\Gateways;

use App\Models\Escrow;
use RuntimeException;

class InMemoryEscrowGateway implements EscrowGateway
{
    /** @var array<int, array{amount:float,captured:float,refunded:float,currency:string,reference:string}> */
    protected array $store = [];

    public function createHold(Escrow $escrow, float $amount, string $currency, array $metadata = []): array
    {
        $reference = 'test_' . uniqid('escrow_', true);
        $this->store[$escrow->getKey()] = [
            'amount' => $amount,
            'captured' => 0.0,
            'refunded' => 0.0,
            'currency' => $currency,
            'reference' => $reference,
        ];

        return [
            'reference' => $reference,
            'status' => 'requires_capture',
            'raw' => [
                'id' => $reference,
                'amount' => $amount,
                'currency' => $currency,
            ],
        ];
    }

    public function capture(Escrow $escrow, float $amount, array $metadata = []): array
    {
        $record = $this->getRecord($escrow);
        if (($record['amount'] - $record['captured'] - $record['refunded']) + 0.01 < $amount) {
            throw new RuntimeException('Insufficient funds to capture.');
        }

        $record['captured'] += $amount;
        $this->store[$escrow->getKey()] = $record;

        return [
            'reference' => $record['reference'],
            'status' => 'succeeded',
            'raw' => [
                'id' => $record['reference'],
                'captured' => $record['captured'],
            ],
        ];
    }

    public function refund(Escrow $escrow, float $amount, array $metadata = []): array
    {
        $record = $this->getRecord($escrow);
        if (($record['amount'] - $record['captured'] - $record['refunded']) + 0.01 < $amount) {
            throw new RuntimeException('Insufficient funds to refund.');
        }

        $record['refunded'] += $amount;
        $this->store[$escrow->getKey()] = $record;

        return [
            'reference' => $record['reference'] . '_refund_' . uniqid(),
            'status' => 'succeeded',
            'raw' => [
                'id' => $record['reference'],
                'refunded' => $record['refunded'],
            ],
        ];
    }

    public function cancel(Escrow $escrow, array $metadata = []): array
    {
        $record = $this->getRecord($escrow);
        unset($this->store[$escrow->getKey()]);

        return [
            'reference' => $record['reference'],
            'status' => 'canceled',
            'raw' => [
                'id' => $record['reference'],
                'cancelled' => true,
            ],
        ];
    }

    protected function getRecord(Escrow $escrow): array
    {
        if (!isset($this->store[$escrow->getKey()])) {
            throw new RuntimeException('Escrow record has not been funded.');
        }

        return $this->store[$escrow->getKey()];
    }
}
