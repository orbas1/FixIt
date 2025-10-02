<?php

namespace Database\Factories;

use App\Models\DataAsset;
use App\Models\DpiaRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DpiaRecord>
 */
class DpiaRecordFactory extends Factory
{
    protected $model = DpiaRecord::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'in_review', 'approved', 'mitigation_required']);
        $submittedAt = $status !== 'draft' ? now()->subDays($this->faker->numberBetween(1, 30)) : null;
        $approvedAt = in_array($status, ['approved']) ? now()->subDays($this->faker->numberBetween(1, 10)) : null;

        return [
            'ulid' => (string) Str::ulid(),
            'data_asset_id' => DataAsset::factory(),
            'status' => $status,
            'risk_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'risk_score' => $this->faker->numberBetween(1, 100),
            'assessment_summary' => $this->faker->paragraph(3),
            'mitigation_actions' => [
                ['action' => 'enable encryption at rest', 'owner' => $this->faker->name()],
            ],
            'residual_risks' => [
                ['risk' => 'third-party transfer monitoring', 'status' => 'tracked'],
            ],
            'submitted_at' => $submittedAt,
            'approved_at' => $approvedAt,
            'reviewed_by' => $status === 'draft' ? null : User::factory(),
            'next_review_at' => now()->addMonths($this->faker->numberBetween(6, 12)),
        ];
    }
}
