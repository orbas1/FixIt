<?php

namespace App\Events;

use App\Models\WithdrawRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateWithdrawRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $withdrawRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(WithdrawRequest $withdrawRequest)
    {
        
        $this->withdrawRequest = $withdrawRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {}
}
