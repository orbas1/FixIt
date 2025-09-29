<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThreadMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->public_id,
            'thread_id' => $this->when($this->relationLoaded('thread'), fn () => $this->thread->public_id, $this->thread_id),
            'body' => $this->body,
            'attachments' => $this->attachments ?: [],
            'meta' => $this->meta ?: [],
            'is_system' => (bool) $this->is_system,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar' => $this->author->profile_photo_url ?? null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
