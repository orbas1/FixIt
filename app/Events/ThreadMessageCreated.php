<?php

namespace App\Events;

use App\Models\ThreadMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThreadMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public ThreadMessage $message)
    {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('thread.' . $this->message->thread_id . '.message');
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->public_id,
                'thread_id' => $this->message->thread->public_id,
                'body' => $this->message->body,
                'attachments' => $this->message->attachments,
                'meta' => $this->message->meta,
                'author' => [
                    'id' => $this->message->author->id,
                    'name' => $this->message->author->name,
                    'avatar' => $this->message->author->profile_photo_url ?? null,
                ],
                'created_at' => $this->message->created_at?->toIso8601String(),
            ],
        ];
    }
}
