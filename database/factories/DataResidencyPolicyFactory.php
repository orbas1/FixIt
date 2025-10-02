<?php

namespace Database\Factories;

use App\Models\DataAsset;
use App\Models\DataResidencyPolicy;
use App\Models\DataResidencyZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataResidencyPolicy>
 */
class DataResidencyPolicyFactory extends Factory
{
    protected $model = DataResidencyPolicy::class;

    public function definition(): array
    {
        return [
            'data_asset_id' => DataAsset::factory(),
            'data_residency_zone_id' => DataResidencyZone::factory(),
            'storage_role' => $this->faker->randomElement(['primary', 'replica', 'processing']),
            'lawful_basis' => $this->faker->randomElement(config('data_governance.lawful_bases')),
            'encryption_profile' => $this->faker->randomElement(['aes-256', 'kms-managed', 'hsm-backed']),
            'data_controller' => $this->faker->company(),
            'cross_border_allowed' => $this->faker->boolean(20),
            'transfer_safeguards' => ['scc' => true, 'bcr' => $this->faker->boolean()],
            'audit_controls' => ['frequency' => 'quarterly', 'owner' => $this->faker->jobTitle()],
        ];
    }
}
