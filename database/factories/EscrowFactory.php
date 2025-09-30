<?php

namespace Database\Factories;

use App\Enums\EscrowStatusEnum;
use App\Models\Escrow;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EscrowFactory extends Factory
{
    protected $model = Escrow::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'consumer_id' => User::factory(),
            'provider_id' => User::factory(),
            'status' => EscrowStatusEnum::AWAITING_FUNDING,
            'amount' => 150.00,
            'amount_released' => 0,
            'amount_refunded' => 0,
            'currency' => 'USD',
            'metadata' => [],
        ];
    }
}
