<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'type' => 'buyer_provider',
            'status' => 'open',
            'subject' => $this->faker->sentence(6),
            'opened_by_id' => User::factory(),
            'last_message_at' => null,
        ];
    }
}
