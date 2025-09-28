<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateProviderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $provider;

    /**
     * Create a new event instance.
     */
    public function __construct(User $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {}
}
