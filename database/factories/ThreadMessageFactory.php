<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ThreadMessageFactory extends Factory
{
    protected $model = ThreadMessage::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'thread_id' => Thread::factory(),
            'author_id' => User::factory(),
            'body' => $this->faker->sentence(),
            'attachments' => [],
            'meta' => [],
            'is_system' => false,
            'delivered_at' => now(),
        ];
    }
}
