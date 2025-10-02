<?php

namespace Database\Factories;

use App\Models\DataResidencyZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DataResidencyZone>
 */
class DataResidencyZoneFactory extends Factory
{
    protected $model = DataResidencyZone::class;

    public function definition(): array
    {
        $region = $this->faker->randomElement(['EU', 'US', 'APAC', 'LATAM', 'MEA']);
        $code = $region . '-' . $this->faker->unique()->numberBetween(1, 999);

        return [
            'ulid' => (string) Str::ulid(),
            'code' => strtoupper($code),
            'name' => $region . ' Residency Zone ' . $this->faker->randomLetter(),
            'region' => $region,
            'country_codes' => $this->faker->randomElements([
                'US', 'CA', 'DE', 'FR', 'BR', 'SG', 'IN', 'ZA', 'JP', 'AU',
            ], $this->faker->numberBetween(1, 4)),
            'default_controller' => $this->faker->company(),
            'approved_services' => $this->faker->randomElements([
                'aws', 'gcp', 'azure', 'on_prem', 'digitalocean',
            ], $this->faker->numberBetween(1, 3)),
            'risk_rating' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
