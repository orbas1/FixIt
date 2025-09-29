<?php

namespace Database\Factories;

use App\Enums\BidStatusEnum;
use App\Models\Bid;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'provider_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 40, 400),
            'description' => $this->faker->sentence(12),
            'status' => BidStatusEnum::REQUESTED,
        ];
    }

    public function accepted(): self
    {
        return $this->state(fn () => ['status' => BidStatusEnum::ACCEPTED]);
    }

    public function rejected(): self
    {
        return $this->state(fn () => ['status' => BidStatusEnum::REJECTED]);
    }
}
