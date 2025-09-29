<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThreadParticipantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->user_id,
            'role' => $this->role,
            'is_active' => (bool) $this->is_active,
            'last_read_at' => $this->last_read_at?->toIso8601String(),
            'muted_until' => $this->muted_until?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->profile_photo_url ?? null,
            ]),
        ];
    }
}
