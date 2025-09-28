<?php

namespace App\Events;

use App\Models\ExtraCharge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddExtraChargeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $extraCharge;

    /**
     * Create a new event instance.
     */
    public function __construct(ExtraCharge $extraCharge)
    {
        $this->extraCharge = $extraCharge;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        //
    }
}
