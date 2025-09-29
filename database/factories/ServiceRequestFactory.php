<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        $latitude = $this->faker->latitude();
        $longitude = $this->faker->longitude();

        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'duration' => (string) $this->faker->numberBetween(1, 8),
            'duration_unit' => 'hours',
            'required_servicemen' => $this->faker->numberBetween(1, 4),
            'initial_price' => $this->faker->randomFloat(2, 50, 500),
            'final_price' => null,
            'status' => 'open',
            'service_id' => null,
            'user_id' => User::factory(),
            'provider_id' => null,
            'created_by_id' => User::factory(),
            'booking_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'category_ids' => [$this->faker->numberBetween(1, 8)],
            'attachments' => [],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_coordinates' => [
                'lat' => $latitude,
                'lng' => $longitude,
            ],
        ];
    }

    public function pending(): self
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function closed(): self
    {
        return $this->state(fn () => ['status' => 'closed']);
    }
}
