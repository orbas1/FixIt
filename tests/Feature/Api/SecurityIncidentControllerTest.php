<?php

namespace Tests\Feature\Api;

use App\Enums\RoleEnum;
use App\Models\SecurityIncident;
use App\Models\User;
use App\Notifications\SecurityIncidentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityIncidentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate([
            'name' => RoleEnum::ADMIN,
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_report_security_incident(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN);
        $this->actingAs($admin, 'sanctum');

        $payload = [
            'title' => 'Suspicious OAuth token reuse',
            'severity' => 'high',
            'impact_summary' => 'Multiple sessions were hijacked via leaked refresh token.',
            'mitigation_steps' => 'Revoked tokens, rotated secrets, and patched affected clients.',
            'follow_up_actions' => [
                [
                    'owner' => 'platform-security',
                    'action' => 'Add anomaly detection rule for repeated refresh usage',
                ],
            ],
            'runbook_updates' => [
                [
                    'section' => 'OAuth Response Runbook',
                    'change_log' => 'Added refresh token rotation after detection phase.',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/security/incidents', $payload);

        $response->assertCreated();
        $response->assertJsonStructure(['data' => ['id', 'title', 'severity', 'status', 'timeline']]);

        $publicId = $response->json('data.id');
        $this->assertNotEmpty($publicId);

        $this->assertDatabaseHas('security_incidents', [
            'public_id' => $publicId,
            'title' => 'Suspicious OAuth token reuse',
            'severity' => 'high',
        ]);

        Notification::assertSentTo(
            [$admin],
            SecurityIncidentNotification::class,
            fn (SecurityIncidentNotification $notification) => $notification->toArray($admin)['event'] === 'reported'
        );
    }

    public function test_incident_can_be_acknowledged_resolved_and_closed(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN);
        $this->actingAs($admin, 'sanctum');

        $incident = SecurityIncident::factory()->create([
            'title' => 'Escalated privilege attempt',
            'severity' => 'critical',
            'impacted_assets' => ['api.fixit.test', 'queue-worker-02'],
            'created_by' => $admin->id,
        ]);

        $acknowledge = $this->postJson(
            "/api/v1/security/incidents/{$incident->public_id}/acknowledge",
            ['note' => 'Triaged by on-call engineer']
        );
        $acknowledge->assertOk();
        $this->assertEquals('acknowledged', $incident->fresh()->status);

        $resolve = $this->postJson(
            "/api/v1/security/incidents/{$incident->public_id}/resolve",
            [
                'root_cause' => 'Misconfigured IAM policy allowed unintended scope.',
                'impact_summary' => 'No customer data accessed; privilege escalation prevented by rate limits.',
                'mitigation_steps' => 'Tightened IAM policy, added policy regression test.',
                'follow_up_actions' => [
                    [
                        'owner' => 'infra',
                        'action' => 'Audit IAM policies in staging',
                        'due_at' => now()->addWeek()->toDateString(),
                    ],
                ],
            ]
        );

        $resolve->assertOk();
        $resolvedIncident = $incident->fresh();
        $this->assertEquals('resolved', $resolvedIncident->status);
        $this->assertNotNull($resolvedIncident->resolved_at);
        $this->assertEquals('Misconfigured IAM policy allowed unintended scope.', $resolvedIncident->root_cause);
        $this->assertCount(2, $resolvedIncident->timeline);

        $close = $this->postJson(
            "/api/v1/security/incidents/{$incident->public_id}/close",
            ['note' => 'Postmortem delivered and accepted by compliance.']
        );
        $close->assertOk();
        $this->assertEquals('closed', $incident->fresh()->status);

        Notification::assertSentTimes(SecurityIncidentNotification::class, 3);
    }
}
