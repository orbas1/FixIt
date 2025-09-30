<?php

namespace Tests\Unit\Services\Payments;

use App\Enums\EscrowStatusEnum;
use App\Models\Escrow;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Compliance\ComplianceReporter;
use App\Services\Escrow\EscrowLedgerService;
use App\Services\Escrow\EscrowService;
use App\Services\Escrow\Gateways\EscrowGateway;
use App\Services\Payments\EscrowPaymentIntentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\StripeClient;
use Tests\TestCase;

class EscrowPaymentIntentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatePaymentIntentStoresMetadataAndReturnsClientSecret(): void
    {
        config(['services.stripe.key' => 'pk_test_123']);

        $consumer = User::factory()->create([
            'stripe_customer_id' => 'cus_test',
            'default_payment_method_id' => 'pm_123',
        ]);
        $provider = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $consumer->id,
        ]);
        $escrow = Escrow::factory()
            ->for($serviceRequest)
            ->create([
                'consumer_id' => $consumer->id,
                'provider_id' => $provider->id,
                'amount' => 120.55,
                'currency' => 'USD',
            ]);

        $stripe = $this->mockStripeClient([
            'id' => 'pi_test',
            'client_secret' => 'secret_123',
            'currency' => 'usd',
            'amount' => 12055,
            'metadata' => [],
            'livemode' => false,
        ], [
            'secret' => 'ephkey_123',
        ]);

        $service = $this->makeService($stripe);

        $intent = $service->createPaymentIntent($escrow, $consumer);

        $this->assertSame('pi_test', $intent['payment_intent_id']);
        $this->assertSame('pk_test_123', $intent['publishable_key']);

        $escrow->refresh();
        $this->assertSame(EscrowStatusEnum::AWAITING_FUNDING, $escrow->status);
        $this->assertSame('pi_test', $escrow->metadata['payment_intent']['id']);
    }

    public function testHandleSucceededPaymentIntentMarksEscrowFunded(): void
    {
        $consumer = User::factory()->create();
        $provider = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $consumer->id,
        ]);

        $escrow = Escrow::factory()
            ->for($serviceRequest)
            ->create([
                'consumer_id' => $consumer->id,
                'provider_id' => $provider->id,
                'amount' => 75.00,
                'currency' => 'USD',
                'metadata' => [
                    'payment_intent' => [
                        'id' => 'pi_success',
                        'client_secret' => 'secret',
                    ],
                ],
            ]);

        $stripe = $this->mockStripeClient([
            'id' => 'pi_success',
            'client_secret' => 'secret',
            'currency' => 'usd',
            'amount' => 7500,
            'metadata' => [],
            'livemode' => false,
        ], null, false);

        $service = $this->makeService($stripe);

        $payload = [
            'id' => 'pi_success',
            'metadata' => ['escrow_id' => $escrow->id],
            'amount_received' => 7500,
            'currency' => 'usd',
            'status' => 'succeeded',
            'charges' => ['data' => []],
            'latest_charge' => 'ch_123',
            'created' => now()->timestamp,
        ];

        $service->handleSucceededPaymentIntent($payload);

        $escrow->refresh();
        $this->assertSame(EscrowStatusEnum::FUNDED, $escrow->status);
        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $escrow->id,
            'type' => 'hold',
            'reference' => 'pi_success',
        ]);
    }

    private function makeService(StripeClient $stripe): EscrowPaymentIntentService
    {
        $ledger = app(EscrowLedgerService::class);
        $gateway = Mockery::mock(EscrowGateway::class);
        $compliance = Mockery::mock(ComplianceReporter::class);
        $compliance->shouldReceive('report')->andReturnNull();

        $escrowService = new EscrowService($gateway, $ledger, $compliance);

        return new EscrowPaymentIntentService($stripe, $escrowService, $ledger);
    }

    private function mockStripeClient(array $intentData, ?array $ephemeralKeyData = null, bool $expectEphemeral = true): StripeClient
    {
        $stripe = Mockery::mock(StripeClient::class);

        $intent = new class($intentData)
        {
            public function __construct(private array $data)
            {
            }

            public function __get(string $name)
            {
                return $this->data[$name] ?? null;
            }

            public function toArray(): array
            {
                return $this->data;
            }
        };

        $paymentIntents = Mockery::mock();
        $paymentIntents->shouldReceive('create')->andReturn($intent);
        $stripe->paymentIntents = $paymentIntents;

        $ephemeralKeys = Mockery::mock();
        if ($expectEphemeral && $ephemeralKeyData !== null) {
            $ephemeralKeys->shouldReceive('create')->andReturn(new class($ephemeralKeyData)
            {
                public function __construct(private array $data)
                {
                }

                public function __get(string $name)
                {
                    return $this->data[$name] ?? null;
                }

                public function toArray(): array
                {
                    return $this->data;
                }
            });
        } else {
            $ephemeralKeys->shouldReceive('create')->andReturn(null);
        }

        $stripe->ephemeralKeys = $ephemeralKeys;

        $paymentIntents->shouldReceive('retrieve')->andReturn($intent);

        return $stripe;
    }
}
