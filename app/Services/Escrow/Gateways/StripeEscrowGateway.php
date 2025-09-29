<?php

namespace App\Services\Escrow\Gateways;

use App\Models\Escrow;
use RuntimeException;
use Stripe\StripeClient;

class StripeEscrowGateway implements EscrowGateway
{
    public function __construct(private readonly StripeClient $client)
    {
    }

    public function createHold(Escrow $escrow, float $amount, string $currency, array $metadata = []): array
    {
        $this->assertPaymentMethodAvailable($escrow);

        $intent = $this->client->paymentIntents->create([
            'amount' => $this->toStripeAmount($amount, $currency),
            'currency' => strtolower($currency),
            'customer' => $escrow->consumer->stripe_customer_id ?? null,
            'payment_method' => $escrow->consumer->default_payment_method_id ?? null,
            'capture_method' => config('escrow.stripe.capture_method', 'manual'),
            'metadata' => array_merge($metadata, [
                'escrow_id' => (string) $escrow->getKey(),
            ]),
            'description' => sprintf('Escrow hold for job #%s', $escrow->service_request_id),
        ], $this->idempotencyOptions($metadata));

        return [
            'reference' => $intent->id,
            'status' => $intent->status,
            'raw' => $intent->toArray(),
        ];
    }

    public function capture(Escrow $escrow, float $amount, array $metadata = []): array
    {
        $reference = $escrow->hold_reference;
        if (!$reference) {
            throw new RuntimeException('Escrow hold reference missing.');
        }

        $intent = $this->client->paymentIntents->capture($reference, [
            'amount_to_capture' => $this->toStripeAmount($amount, $escrow->currency),
            'metadata' => array_merge($metadata, [
                'escrow_id' => (string) $escrow->getKey(),
            ]),
        ], $this->idempotencyOptions($metadata));

        return [
            'reference' => $intent->id,
            'status' => $intent->status,
            'raw' => $intent->toArray(),
        ];
    }

    public function refund(Escrow $escrow, float $amount, array $metadata = []): array
    {
        $reference = $escrow->hold_reference;
        if (!$reference) {
            throw new RuntimeException('Escrow hold reference missing.');
        }

        $refund = $this->client->refunds->create([
            'payment_intent' => $reference,
            'amount' => $this->toStripeAmount($amount, $escrow->currency),
            'metadata' => array_merge($metadata, [
                'escrow_id' => (string) $escrow->getKey(),
            ]),
        ], $this->idempotencyOptions($metadata));

        return [
            'reference' => $refund->id,
            'status' => $refund->status,
            'raw' => $refund->toArray(),
        ];
    }

    public function cancel(Escrow $escrow, array $metadata = []): array
    {
        $reference = $escrow->hold_reference;
        if (!$reference) {
            throw new RuntimeException('Escrow hold reference missing.');
        }

        $intent = $this->client->paymentIntents->cancel($reference, [
            'cancellation_reason' => $metadata['reason'] ?? 'requested_by_customer',
        ], $this->idempotencyOptions($metadata));

        return [
            'reference' => $intent->id,
            'status' => $intent->status,
            'raw' => $intent->toArray(),
        ];
    }

    protected function toStripeAmount(float $amount, string $currency): int
    {
        $currency = strtolower($currency);
        $zeroDecimalCurrencies = config('escrow.stripe.zero_decimal_currencies', []);

        if (in_array($currency, $zeroDecimalCurrencies, true)) {
            return (int) round($amount, 0);
        }

        return (int) round($amount * 100);
    }

    protected function idempotencyOptions(array $metadata): array
    {
        if (!isset($metadata['idempotency_key'])) {
            return [];
        }

        return ['idempotency_key' => (string) $metadata['idempotency_key']];
    }

    protected function assertPaymentMethodAvailable(Escrow $escrow): void
    {
        if (!$escrow->consumer || !$escrow->consumer->default_payment_method_id) {
            throw new RuntimeException('Consumer payment method is not on file.');
        }
    }
}
