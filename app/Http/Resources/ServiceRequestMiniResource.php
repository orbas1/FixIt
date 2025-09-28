<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestMiniResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this?->id,
            'title' => $this?->title,
            'status' => $this?->status,
            'initial_price' => $this?->initial_price,
            'booking_date' => $this?->booking_date,
            'media' => $this?->media ? $this?->media->map(function ($media) {
                return [
                    'id' => $media?->id,
                    'original_url' => $media?->original_url,
                    'collection_name' => $media?->collection_name,
                ];
            }) : [],
        ];
    }
}
