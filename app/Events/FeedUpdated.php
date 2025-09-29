<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $action,
        public readonly string $subjectType,
        public readonly int $subjectId,
        public readonly array $payload = []
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('feed.global');
    }

    public function broadcastAs(): string
    {
        return 'feed.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'data' => $this->payload,
        ];
    }
}
