<?php

namespace Database\Factories;

use App\Enums\SecurityIncidentSeverity;
use App\Enums\SecurityIncidentStatus;
use App\Models\SecurityIncident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SecurityIncidentFactory extends Factory
{
    protected $model = SecurityIncident::class;

    public function definition(): array
    {
        $detectedAt = $this->faker->dateTimeBetween('-2 days', 'now');

        return [
            'public_id' => (string) Str::uuid(),
            'title' => $this->faker->sentence(6),
            'severity' => $this->faker->randomElement(SecurityIncidentSeverity::values()),
            'status' => SecurityIncidentStatus::OPEN->value,
            'detection_source' => $this->faker->randomElement(['ids', 'monitoring', 'customer_report', 'bug_bounty']),
            'impacted_assets' => [$this->faker->domainName(), $this->faker->domainWord()],
            'impact_summary' => $this->faker->paragraph(),
            'mitigation_steps' => $this->faker->paragraphs(2, true),
            'follow_up_actions' => [
                ['owner' => 'secops', 'action' => 'Rotate credentials', 'due_at' => now()->addDays(3)->toIso8601String()],
            ],
            'detected_at' => $detectedAt,
            'created_by' => User::factory(),
        ];
    }

    public function acknowledged(): self
    {
        return $this->state(function () {
            $ackAt = now();

            return [
                'status' => SecurityIncidentStatus::ACKNOWLEDGED->value,
                'acknowledged_at' => $ackAt,
                'acknowledged_by' => User::factory(),
            ];
        });
    }

    public function resolved(): self
    {
        return $this->state(function () {
            $resolvedAt = now();

            return [
                'status' => SecurityIncidentStatus::RESOLVED->value,
                'acknowledged_at' => $resolvedAt->copy()->subHour(),
                'resolved_at' => $resolvedAt,
                'acknowledged_by' => User::factory(),
                'resolved_by' => User::factory(),
                'root_cause' => 'Misconfigured firewall rule allowed lateral movement.',
            ];
        });
    }
}
