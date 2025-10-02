<?php

namespace Tests\Feature\Security;

use App\Enums\RoleEnum;
use App\Models\NetworkZone;
use App\Models\User;
use App\Services\Security\ZeroTrustEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ZeroTrustEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_denies_when_privileged_user_has_no_mfa(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate(RoleEnum::ADMIN, 'web');
        $user->assignRole(RoleEnum::ADMIN);

        config(['zero_trust.enforcement.require_mfa_roles' => [RoleEnum::ADMIN]]);

        $decision = app(ZeroTrustEvaluator::class)->evaluate($user, [
            'ip' => '203.0.113.10',
            'user_agent' => 'PHPUnit',
            'device_identifier' => 'test-device',
        ]);

        $this->assertSame('deny', $decision->decision());
        $this->assertDatabaseHas('zero_trust_access_events', [
            'user_id' => $user->id,
            'decision' => 'deny',
        ]);
    }

    public function test_allows_request_on_trusted_network_with_recent_mfa(): void
    {
        $user = User::factory()->create([
            'mfa_secret' => 'JBSWY3DPEHPK3PXP',
            'mfa_enabled_at' => now()->subDay(),
            'mfa_last_used_at' => now()->subMinutes(5),
        ]);

        NetworkZone::create([
            'slug' => 'corp',
            'name' => 'Corporate VPN',
            'type' => 'trusted',
            'risk_level' => 10,
            'ip_ranges' => ['10.0.0.0/8'],
            'enforced_controls' => ['mfa', 'device_trust'],
            'is_active' => true,
        ]);

        $decision = app(ZeroTrustEvaluator::class)->evaluate($user, [
            'ip' => '10.1.1.5',
            'user_agent' => 'PHPUnit',
            'device_identifier' => 'vpn-device',
            'mfa_verified_at' => now()->subMinutes(5),
        ]);

        $this->assertSame('allow', $decision->decision());
        $this->assertDatabaseHas('zero_trust_access_events', [
            'user_id' => $user->id,
            'decision' => 'allow',
        ]);
    }
}
