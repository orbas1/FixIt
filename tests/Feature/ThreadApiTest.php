<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\ThreadParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_threads(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $thread = Thread::factory()->create([
            'opened_by_id' => $user->id,
            'last_message_at' => now(),
        ]);

        ThreadParticipant::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'role' => 'consumer',
        ]);

        ThreadParticipant::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $other->id,
            'role' => 'provider',
        ]);

        ThreadMessage::factory()->create([
            'thread_id' => $thread->id,
            'author_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/threads');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $thread->public_id)
            ->assertJsonPath('data.0.participants.0.id', $user->id);
    }

    public function test_user_can_create_thread(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $payload = [
            'type' => 'buyer_provider',
            'participants' => [$other->id],
            'subject' => 'Kitchen renovation',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/threads', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('threads', ['subject' => 'Kitchen renovation']);
        $this->assertDatabaseHas('thread_participants', ['user_id' => $user->id]);
        $this->assertDatabaseHas('thread_participants', ['user_id' => $other->id]);
    }

    public function test_participant_can_send_message(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $thread = Thread::factory()->create(['opened_by_id' => $user->id]);
        ThreadParticipant::factory()->create(['thread_id' => $thread->id, 'user_id' => $user->id]);
        ThreadParticipant::factory()->create(['thread_id' => $thread->id, 'user_id' => $other->id, 'role' => 'provider']);

        $response = $this->actingAs($user, 'sanctum')->postJson(
            "/api/threads/{$thread->public_id}/messages",
            ['body' => 'Hello there']
        );

        $response->assertCreated()
            ->assertJsonPath('data.body', 'Hello there');

        $this->assertDatabaseHas('thread_messages', [
            'thread_id' => $thread->id,
            'body' => 'Hello there',
        ]);
    }

    public function test_non_participant_cannot_view_thread(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $stranger = User::factory()->create();

        $thread = Thread::factory()->create(['opened_by_id' => $user->id]);
        ThreadParticipant::factory()->create(['thread_id' => $thread->id, 'user_id' => $user->id]);
        ThreadParticipant::factory()->create(['thread_id' => $thread->id, 'user_id' => $other->id]);

        $this->actingAs($stranger, 'sanctum')
            ->getJson("/api/threads/{$thread->public_id}")
            ->assertForbidden();
    }
}
