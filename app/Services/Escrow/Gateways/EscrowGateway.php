<?php

namespace App\Services\Escrow\Gateways;

use App\Models\Escrow;

interface EscrowGateway
{
    /**
     * Create a hold/authorization on the user's payment method.
     *
     * @return array{reference:string,status:string,raw:array}
     */
    public function createHold(Escrow $escrow, float $amount, string $currency, array $metadata = []): array;

    /**
     * Capture funds from the hold.
     *
     * @return array{reference:string,status:string,raw:array}
     */
    public function capture(Escrow $escrow, float $amount, array $metadata = []): array;

    /**
     * Refund funds to the customer.
     *
     * @return array{reference:string,status:string,raw:array}
     */
    public function refund(Escrow $escrow, float $amount, array $metadata = []): array;

    /**
     * Cancel the hold entirely.
     *
     * @return array{reference:string,status:string,raw:array}
     */
    public function cancel(Escrow $escrow, array $metadata = []): array;
}
