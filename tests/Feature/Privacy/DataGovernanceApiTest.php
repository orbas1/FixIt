<?php

namespace Tests\Feature\Privacy;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataGovernanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_asset_and_trigger_dpia(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate(RoleEnum::ADMIN, 'web');
        $user->assignRole(RoleEnum::ADMIN);

        $zoneAttributes = [
            'code' => 'EU-CORP',
            'name' => 'EU Corporate Residency',
            'region' => 'EU',
            'country_codes' => ['DE', 'FR', 'IE'],
            'default_controller' => 'FixIt Operations LLC',
            'approved_services' => ['aws', 'azure'],
            'risk_rating' => 2,
            'is_active' => true,
        ];

        $this->actingAs($user, 'sanctum');
        config([
            'zero_trust.enforcement.require_mfa_roles' => [],
            'zero_trust.risk.new_device_penalty' => 0,
            'zero_trust.risk.unknown_network_penalty' => 0,
            'zero_trust.risk.challenge_threshold' => 90,
        ]);

        $zoneResponse = $this->postJson('/api/v1/governance/data-residency-zones', $zoneAttributes);
        $zoneResponse->assertCreated();
        $zoneId = $zoneResponse->json('data.id');

        $assetPayload = [
            'data_domain_id' => null,
            'steward_id' => $user->id,
            'key' => 'IDENTITY_CORE',
            'name' => 'Identity Core Profile',
            'classification' => 'confidential',
            'processing_purpose' => 'Provide login, verification, and account management.',
            'data_elements' => ['email', 'phone_number', 'address'],
            'lawful_bases' => ['contract', 'legal_obligation'],
            'retention_period_days' => 365,
            'requires_dpia' => true,
            'monitoring_controls' => ['logs' => 'centralized'],
            'residency_policies' => [[
                'zone_code' => $zoneAttributes['code'],
                'storage_role' => 'primary',
                'lawful_basis' => 'contract',
                'encryption_profile' => 'aes-256',
                'data_controller' => 'FixIt Operations LLC',
                'cross_border_allowed' => false,
                'transfer_safeguards' => ['scc' => true],
                'audit_controls' => ['frequency' => 'quarterly'],
            ]],
        ];

        $assetResponse = $this->postJson('/api/v1/governance/data-assets', $assetPayload);
        $assetResponse->assertCreated();
        $assetId = $assetResponse->json('data.id');
        $this->assertDatabaseHas('data_assets', [
            'id' => $assetId,
            'key' => 'IDENTITY_CORE',
            'classification' => 'confidential',
            'requires_dpia' => true,
        ]);

        $ensureResponse = $this->postJson(
            sprintf('/api/v1/governance/data-assets/%d/dpia/ensure', $assetId),
            [
                'mitigation_actions' => [
                    ['action' => 'Enable HSM encryption', 'owner' => 'Security Team'],
                ],
                'residual_risks' => [
                    ['risk' => 'Vendor audit pending', 'status' => 'tracked'],
                ],
            ]
        );

        $ensureResponse->assertOk();
        $ensureResponse->assertJsonPath('data.dpia_records.0.mitigation_actions.0.action', 'Enable HSM encryption');
        $ensureResponse->assertJsonPath('data.dpia_records.0.residual_risks.0.risk', 'Vendor audit pending');

        $this->assertDatabaseHas('dpia_records', [
            'data_asset_id' => $assetId,
        ]);
    }
}
