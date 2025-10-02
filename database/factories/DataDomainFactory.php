<?php

namespace Database\Factories;

use App\Models\DataDomain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DataDomain>
 */
class DataDomainFactory extends Factory
{
    protected $model = DataDomain::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'ulid' => (string) Str::ulid(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 999)),
            'description' => $this->faker->sentence(8),
            'data_categories' => $this->faker->randomElements([
                'personal_data',
                'financial_data',
                'behavioral_data',
                'operational_data',
                'safety_data',
            ], $this->faker->numberBetween(1, 3)),
            'is_active' => true,
        ];
    }
}
