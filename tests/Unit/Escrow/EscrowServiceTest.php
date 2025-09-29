<?php

namespace Tests\Unit\Escrow;

use App\Enums\EscrowStatusEnum;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['escrow.gateway' => 'in-memory']);
        ServiceRequest::unsetEventDispatcher();
        ServiceRequest::flushEventListeners();
    }

    public function test_fund_release_and_refund_flow(): void
    {
        $consumer = User::factory()->create([
            'stripe_customer_id' => 'cus_test',
            'default_payment_method_id' => 'pm_test',
        ]);
        $provider = User::factory()->create();

        $job = ServiceRequest::factory()->create([
            'user_id' => $consumer->getKey(),
            'provider_id' => $provider->getKey(),
        ]);

        /** @var EscrowService $service */
        $service = app(EscrowService::class);

        $escrow = $service->initialize($job, $consumer, $provider, 150.0, 'USD');
        $service->fund($escrow, 150.0, $consumer);

        $escrow->refresh();
        $this->assertSame(EscrowStatusEnum::FUNDED, $escrow->status);
        $this->assertEquals(0.0, $escrow->amount_released);
        $this->assertEquals(0.0, $escrow->amount_refunded);

        $service->release($escrow, 100.0, $provider);
        $escrow->refresh();
        $this->assertSame(100.0, $escrow->amount_released);
        $this->assertSame(EscrowStatusEnum::PARTIALLY_RELEASED, $escrow->status);
        $this->assertEquals(50.0, $escrow->available_amount);

        $service->refund($escrow, 50.0, $consumer);
        $escrow->refresh();

        $this->assertSame(50.0, $escrow->amount_refunded);
        $this->assertSame(EscrowStatusEnum::REFUNDED, $escrow->status);
        $this->assertEquals(0.0, $escrow->available_amount);
        $this->assertCount(3, $escrow->transactions);
    }
}
