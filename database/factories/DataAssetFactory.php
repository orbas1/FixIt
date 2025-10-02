<?php

namespace Database\Factories;

use App\Models\DataAsset;
use App\Models\DataDomain;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DataAsset>
 */
class DataAssetFactory extends Factory
{
    protected $model = DataAsset::class;

    public function definition(): array
    {
        return [
            'ulid' => (string) Str::ulid(),
            'data_domain_id' => DataDomain::factory(),
            'steward_id' => User::factory(),
            'key' => strtoupper(Str::random(8)),
            'name' => ucfirst($this->faker->unique()->words(4, true)),
            'classification' => $this->faker->randomElement(config('data_governance.classifications')),
            'processing_purpose' => $this->faker->sentence(6),
            'data_elements' => $this->faker->randomElements([
                'email', 'phone_number', 'address', 'payment_token', 'geo_location', 'device_id',
            ], $this->faker->numberBetween(2, 5)),
            'lawful_bases' => $this->faker->randomElements(config('data_governance.lawful_bases'), $this->faker->numberBetween(1, 3)),
            'retention_period_days' => $this->faker->numberBetween(30, 1825),
            'requires_dpia' => $this->faker->boolean(30),
            'residency_exceptions' => null,
            'monitoring_controls' => [
                'logs' => 'centralized',
                'telemetry' => 'sentry',
            ],
            'next_review_at' => now()->addMonths($this->faker->numberBetween(6, 18)),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
