<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestHomeResource extends JsonResource
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
            'title' => $this?->title,
            'description' => $this?->description,
            'duration' => $this?->duration,
            'duration_unit' => $this?->duration_unit,
            'required_servicemen' => $this?->required_servicemen,
            'initial_price' => $this?->initial_price,
            'final_price' => $this?->final_price,
            'service_id' => $this?->service_id,
            'user_id' => $this?->user_id,
            'provider_id' => $this?->provider_id,
            'created_by_id' => $this?->created_by_id,
            'booking_date' => $this?->booking_date,
            'category_ids' => $this?->category_ids,
            'locations' => $this?->locations,
            'created_at' => $this?->created_at,
            'status' => $this?->status,
            'media' => $this?->media,
            'location_coordinates' => $this?->location_coordinates,
            'user' => [
                'id' => $this?->user?->id ?? null,
                'name' => $this?->user?->name ?? null,
                'email' => $this?->user?->email ?? null,
            ],
            'bids_count' => $this?->bids?->count(),
            'bids' => $this?->bids,
            'service' => [
                'id' => $this?->service?->id ?? null,
                'title' => $this?->service?->title ?? null,
                'user' => [
                    'id' => $this?->service?->user?->id ?? null,
                    'name' => $this?->service?->user?->name ?? null,
                    'media' => $this?->user?->media ?? null,
                ],
            ],
        ];
    }

}
