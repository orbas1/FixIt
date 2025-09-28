<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this?->id,
            'name' => $this?->name,
            'expertise' => $this?->expertise->pluck('title'),
            'review_ratings' => $this?->review_ratings,
            'media' => $this?->media->map(function ($media) {
                return $media?->getUrl();
            }),
            'primary_address' => [
                'id' => $this?->id,
                'address' => $this?->primaryAddress?->address ?? null,
                'area' => $this?->primaryAddress?->area ?? null,
                'city' => $this?->primaryAddress?->city ?? null,
            ],
        ];
    }
}
