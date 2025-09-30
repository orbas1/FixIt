<?php

namespace Tests\Unit\Services\Security;

use App\Jobs\Security\ProcessDataDeletionRequest;
use App\Models\User;
use App\Services\Security\PrivacyComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PrivacyComplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_and_returns_preferences(): void
    {
        Bus::fake();
        $service = new PrivacyComplianceService();
        $user = User::factory()->create();

        $service->updatePreferences($user, [
            'marketing' => true,
            'analytics' => false,
        ]);

        $preferences = $service->getPreferences($user->fresh('privacyConsents'));

        $this->assertTrue($preferences['marketing']['granted']);
        $this->assertFalse($preferences['analytics']['granted']);
        $this->assertTrue($preferences['essential']['granted']);
    }

    public function test_it_dispatches_deletion_job(): void
    {
        Bus::fake();
        $service = new PrivacyComplianceService();
        $user = User::factory()->create();

        $token = $service->requestDataDeletion($user);

        Bus::assertDispatched(ProcessDataDeletionRequest::class, function ($job) use ($user, $token) {
            $reflection = new \ReflectionClass($job);
            $userId = $reflection->getProperty('userId');
            $userId->setAccessible(true);
            $jobToken = $reflection->getProperty('token');
            $jobToken->setAccessible(true);

            return $userId->getValue($job) === $user->id && $jobToken->getValue($job) === $token;
        });
    }
}
