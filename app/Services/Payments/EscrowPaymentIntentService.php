<?php

namespace App\Services\Payments;

use App\Enums\EscrowStatusEnum;
use App\Enums\EscrowTransactionTypeEnum;
use App\Models\Escrow;
use App\Models\User;
use App\Services\Escrow\EscrowLedgerService;
use App\Services\Escrow\EscrowService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class EscrowPaymentIntentService
{
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly EscrowService $escrowService,
        private readonly EscrowLedgerService $ledger,
    ) {
    }

    public function createPaymentIntent(Escrow $escrow, User $actor, array $context = []): array
    {
        if ($escrow->consumer_id !== $actor->getKey()) {
            throw new RuntimeException('Only the consumer can fund this escrow.');
        }

        if (in_array($escrow->status, EscrowStatusEnum::TERMINAL_STATES, true)) {
            throw new RuntimeException('Escrow is already closed.');
        }

        if ($escrow->status === EscrowStatusEnum::FUNDED) {
            return $this->buildIntentPayloadFromExisting($escrow);
        }

        $amount = $escrow->amount;
        if ($amount <= 0) {
            throw new RuntimeException('Escrow amount must be greater than zero.');
        }

        $existing = $this->resolveExistingIntent($escrow);
        if ($existing) {
            return $existing;
        }

        $metadata = array_merge(
            Arr::get($escrow->metadata, 'payment_intent.context', []),
            Arr::only($context, ['source', 'session_id'])
        );

        $intent = $this->stripe->paymentIntents->create([
            'amount' => $this->toStripeAmount($amount, $escrow->currency),
            'currency' => strtolower($escrow->currency),
            'capture_method' => config('escrow.stripe.capture_method', 'manual'),
            'confirmation_method' => 'automatic',
            'automatic_payment_methods' => ['enabled' => true],
            'customer' => $escrow->consumer?->stripe_customer_id,
            'metadata' => array_merge($metadata, [
                'escrow_id' => (string) $escrow->getKey(),
                'service_request_id' => (string) $escrow->service_request_id,
            ]),
            'statement_descriptor_suffix' => Str::limit('FixIt Escrow ' . $escrow->service_request_id, 22, ''),
        ], [
            'idempotency_key' => $this->idempotencyKey($escrow, $context),
        ]);

        $ephemeralKey = null;
        if ($escrow->consumer?->stripe_customer_id) {
            $ephemeralKey = $this->stripe->ephemeralKeys->create([
                'customer' => $escrow->consumer->stripe_customer_id,
            ], [
                'stripe_version' => config('services.stripe.api_version', '2024-10-22'),
            ]);
        }

        $metadataPayload = [
            'payment_intent' => [
                'id' => $intent->id,
                'client_secret' => $intent->client_secret,
                'created_at' => Carbon::now()->toIso8601String(),
                'context' => $metadata,
            ],
        ];

        $escrow->metadata = array_merge($escrow->metadata ?? [], $metadataPayload);
        $escrow->status = EscrowStatusEnum::AWAITING_FUNDING;
        $escrow->save();

        return $this->buildIntentPayload($intent->toArray(), $ephemeralKey?->toArray());
    }

    public function handleSucceededPaymentIntent(array $intentPayload): ?Escrow
    {
        $escrowId = Arr::get($intentPayload, 'metadata.escrow_id');
        if (! $escrowId) {
            return null;
        }

        /** @var Escrow|null $escrow */
        $escrow = Escrow::query()->with(['consumer'])->find($escrowId);
        if (! $escrow) {
            Log::warning('Stripe webhook referenced missing escrow', [
                'escrow_id' => $escrowId,
                'intent' => Arr::get($intentPayload, 'id'),
            ]);

            return null;
        }

        $amountReceived = Arr::get($intentPayload, 'amount_received') ?? Arr::get($intentPayload, 'amount');
        $amount = $this->fromStripeAmount((int) $amountReceived, $escrow->currency);

        $metadata = [
            'gateway_status' => Arr::get($intentPayload, 'status'),
            'payment_method' => Arr::get($intentPayload, 'payment_method_types', []),
            'charges' => Arr::get($intentPayload, 'charges.data', []),
        ];

        $escrow = $this->escrowService->markFundedFromPaymentIntent(
            $escrow,
            $amount,
            Arr::get($intentPayload, 'id'),
            $metadata,
        );

        $this->ledger->record(
            $escrow,
            EscrowTransactionTypeEnum::HOLD,
            $amount,
            'credit',
            null,
            $metadata,
            Arr::get($intentPayload, 'id'),
            Arr::get($intentPayload, 'latest_charge'),
            'Escrow funded via Stripe PaymentIntent',
            Carbon::createFromTimestamp(Arr::get($intentPayload, 'created', time())),
        );

        return $escrow;
    }

    public function handleFailedPaymentIntent(array $intentPayload): void
    {
        $escrowId = Arr::get($intentPayload, 'metadata.escrow_id');
        if (! $escrowId) {
            return;
        }

        Escrow::query()->whereKey($escrowId)->update([
            'status' => EscrowStatusEnum::REQUIRES_ACTION,
            'metadata->payment_intent->last_failure' => [
                'code' => Arr::get($intentPayload, 'last_payment_error.code'),
                'message' => Arr::get($intentPayload, 'last_payment_error.message'),
                'created_at' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }

    protected function resolveExistingIntent(Escrow $escrow): ?array
    {
        $intentId = Arr::get($escrow->metadata, 'payment_intent.id');
        if (! $intentId) {
            return null;
        }

        try {
            $intent = $this->stripe->paymentIntents->retrieve($intentId);
        } catch (ApiErrorException $exception) {
            Log::warning('Failed retrieving existing PaymentIntent', [
                'escrow_id' => $escrow->getKey(),
                'intent_id' => $intentId,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        $ephemeralKey = null;
        if ($escrow->consumer?->stripe_customer_id) {
            $ephemeralKey = $this->stripe->ephemeralKeys->create([
                'customer' => $escrow->consumer->stripe_customer_id,
            ], [
                'stripe_version' => config('services.stripe.api_version', '2024-10-22'),
            ]);
        }

        return $this->buildIntentPayload($intent->toArray(), $ephemeralKey?->toArray());
    }

    protected function buildIntentPayloadFromExisting(Escrow $escrow): array
    {
        $payload = Arr::get($escrow->metadata, 'payment_intent');
        if (! is_array($payload)) {
            throw new RuntimeException('Escrow metadata missing PaymentIntent.');
        }

        return $this->buildIntentPayload($payload, null);
    }

    protected function buildIntentPayload(array $intent, ?array $ephemeralKey): array
    {
        return [
            'payment_intent_id' => $intent['id'],
            'client_secret' => $intent['client_secret'] ?? Arr::get($intent, 'client_secret'),
            'publishable_key' => (string) config('services.stripe.key'),
            'customer_id' => Arr::get($intent, 'customer'),
            'ephemeral_key' => $ephemeralKey['secret'] ?? null,
            'livemode' => (bool) ($intent['livemode'] ?? false),
            'currency' => strtoupper((string) ($intent['currency'] ?? 'usd')),
            'amount' => $this->fromStripeAmount((int) ($intent['amount'] ?? 0), (string) ($intent['currency'] ?? 'usd')),
            'status' => $intent['status'] ?? null,
            'metadata' => $intent['metadata'] ?? [],
        ];
    }

    protected function idempotencyKey(Escrow $escrow, array $context): string
    {
        return 'escrow:' . $escrow->getKey() . ':fund:' . md5(json_encode([
            'amount' => $escrow->amount,
            'currency' => $escrow->currency,
            'context' => Arr::only($context, ['source', 'session_id']),
        ]));
    }

    protected function toStripeAmount(float $amount, string $currency): int
    {
        $currency = strtolower($currency);
        $zeroDecimals = config('escrow.stripe.zero_decimal_currencies', []);

        return in_array($currency, $zeroDecimals, true)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }

    protected function fromStripeAmount(int $amount, string $currency): float
    {
        $currency = strtolower($currency);
        $zeroDecimals = config('escrow.stripe.zero_decimal_currencies', []);

        return in_array($currency, $zeroDecimals, true)
            ? round($amount, 2)
            : round($amount / 100, 2);
    }
}
