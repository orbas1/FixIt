<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->public_id,
            'subject' => $this->subject,
            'type' => $this->type,
            'status' => $this->status,
            'service_request_id' => $this->service_request_id,
            'booking_id' => $this->booking_id,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'participants' => ThreadParticipantResource::collection($this->whenLoaded('participants')),
            'messages' => ThreadMessageResource::collection($this->whenLoaded('messages')),
            'latest_message' => $this->whenLoaded('latestMessage', fn () => ThreadMessageResource::make($this->latestMessage->loadMissing('thread'))),
        ];
    }
}
