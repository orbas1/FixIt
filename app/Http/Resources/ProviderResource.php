<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Lang') ?? app()->getLocale();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'review_ratings' => $this->review_ratings,
            'is_verified' => $this->is_verified,
            'bookings_count' => $this->bookings_count,
            'is_favourite' => $this->is_favourite,
            'is_favourite_id' => $this->is_favourite_id,
            'code' => $this->code,
            'phone' => $this->phone,
            'experience_interval' => $this->experience_interval,
            'experience_duration' => $this->experience_duration,
            'served' => $this->served ?? 0,
            'location_cordinates' => $this?->location_cordinates,
            'media' => $this->media->map(function ($media) {
                return collect($media)->only(['original_url']);
            }),
            'primary_address' =>$this->PrimaryAddress ?  [
                'address' => $this->PrimaryAddress->address ?? null,
                'area' => $this->PrimaryAddress->area ?? null,
                'city' => $this->PrimaryAddress->city ?? null,
                'latitude' => $this->PrimaryAddress->latitude ?? null,
                'longitude' => $this->PrimaryAddress->longitude ?? null,
            ] : null,
            'known_languages' => $this->knownLanguages ? $this->knownLanguages->map(function($media){
                return [
                    'id' => $media->id,
                    'key' => $media->key
                ];  
            }) : [],
            'zones' => $this?->zones ? $this?->zones->map(function($zone){
                return [
                    'id' => $zone->id,
                    'name' => $zone->name
                ];
            }) : [],
            'expertise' => $this->expertise ? $this->expertise->map(function ($service) use($locale){
                return [
                    'id' => $service->id,
                    'title' => $service->getTranslation('title', $locale),
                ];
            }) : []
        ];
    }
}
