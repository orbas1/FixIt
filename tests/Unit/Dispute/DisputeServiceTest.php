<?php

namespace Tests\Unit\Dispute;

use App\Enums\DisputeStageEnum;
use App\Enums\DisputeStatusEnum;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Dispute\DisputeService;
use App\Services\Escrow\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisputeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['escrow.gateway' => 'in-memory']);
        ServiceRequest::unsetEventDispatcher();
        ServiceRequest::flushEventListeners();
    }

    public function test_dispute_lifecycle_and_settlement(): void
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

        /** @var EscrowService $escrowService */
        $escrowService = app(EscrowService::class);
        $escrow = $escrowService->initialize($job, $consumer, $provider, 120, 'USD');
        $escrowService->fund($escrow, 120, $consumer);

        /** @var DisputeService $disputeService */
        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->open($job, $consumer, $provider, 'quality_issue', $escrow, ['summary' => 'Paint chipped.']);

        $this->assertSame(DisputeStageEnum::EVIDENCE, $dispute->stage);
        $this->assertSame(DisputeStatusEnum::OPEN, $dispute->status);
        $this->assertNotNull($dispute->deadline_at);

        $message = $disputeService->postMessage($dispute, $consumer, 'Uploading photos');
        $this->assertNotNull($message->getKey());

        $dispute = $disputeService->advanceStage($dispute, DisputeStageEnum::MEDIATION, $provider, 'Evidence reviewed');
        $this->assertSame(DisputeStageEnum::MEDIATION, $dispute->stage);
        $this->assertSame(DisputeStatusEnum::UNDER_REVIEW, $dispute->status);

        $resolved = $disputeService->settle($dispute, $provider, 60, 60, ['notes' => 'Split resolution']);
        $this->assertSame(DisputeStageEnum::RESOLVED, $resolved->stage);
        $this->assertSame(DisputeStatusEnum::RESOLVED, $resolved->status);
        $this->assertNotNull($resolved->closed_at);
        $this->assertEquals(60, $resolved->resolution['release']);
        $this->assertEquals(60, $resolved->resolution['refund']);
    }

    public function test_auto_advance_moves_expired_disputes_forward(): void
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

        /** @var EscrowService $escrowService */
        $escrowService = app(EscrowService::class);
        $escrow = $escrowService->initialize($job, $consumer, $provider, 80, 'USD');
        $escrowService->fund($escrow, 80, $consumer);

        /** @var DisputeService $disputeService */
        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->open($job, $consumer, $provider, 'delay', $escrow);

        // Force deadline to be in the past
        $dispute->forceFill([
            'deadline_at' => Carbon::now()->subHours(10),
        ])->save();

        $disputeService->autoAdvanceExpired();

        $dispute->refresh();
        $this->assertSame(DisputeStageEnum::MEDIATION, $dispute->stage);
        $this->assertSame(DisputeStatusEnum::UNDER_REVIEW, $dispute->status);
    }
}
