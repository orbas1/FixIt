<?php

namespace App\Events;

use App\Models\ServicemanWithdrawRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateServicemanWithdrawRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $servicemanWithdrawRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
      
        $this->servicemanWithdrawRequest = $servicemanWithdrawRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {}
}
