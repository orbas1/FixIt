<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\ThreadParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadParticipantFactory extends Factory
{
    protected $model = ThreadParticipant::class;

    public function definition(): array
    {
        return [
            'thread_id' => Thread::factory(),
            'user_id' => User::factory(),
            'role' => 'consumer',
            'is_active' => true,
            'last_read_at' => now(),
        ];
    }
}
