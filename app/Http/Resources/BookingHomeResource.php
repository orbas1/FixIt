<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingHomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Lang') ?? app()->getLocale();
        return [
            'id' => $this?->id,
            'booking_number' => $this?->booking_number,
            'parent_booking_number' => $this?->parent?->booking_number,
            'total_servicemen' => $this?->total_servicemen ?? null,
            'total' => $this?->total ?? null,
            'date_time' => $this?->date_time,
            'payment_method' => $this?->payment_method,
            'payment_status' => $this?->payment_status,
            'total_extra_servicemen' => $this?->total_extra_servicemen,
            'booking_status' => [
                'name' => $this->booking_status->name ?? null,
                'slug' => $this->booking_status->slug ?? null,
            ],
            'service' => [
                'id' => $this?->service?->id,
                'title' => $this?->service?->title,
                'media' => $this?->getFilteredServiceMedia($locale),
            ],
            'consumer' => [
                'id' => $this?->consumer?->id,
                'name' => $this?->consumer?->name,
                'media' => $this?->media ? $this?->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'original_url' => $media->original_url,
                        'collection_name' => $media->collection_name,
                    ];
                }) : [],
            ],
            'servicemen' => $this?->servicemen? $this?->servicemen->map(function ($serviceman) {
                return [
                    'id' => $serviceman->id,
                    'name' => $serviceman->name,
                    'ServicemanReviewRatings' => $serviceman->ServicemanReviewRatings,
                    'media' => $serviceman->media ? $serviceman->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'original_url' => $media->original_url,
                            'collection_name' => $media->collection_name,
                        ];
                    }) : [],
                ];
            }) : [],
            'provider' => [
                'id' => $this?->provider->id,
                'name' => $this?->provider->name,
                'media' => $this?->provider?->media ? $this?->provider?->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'original_url' => $media->original_url,
                        'collection_name' => $media->collection_name,
                    ];
                }) : [],
            ],
            'address' => [
                'address' => $this?->address?->address,
                'area' => $this?->address?->area,
                'city' => $this?->address?->city,
            ],
        ];
    }

    private function getFilteredServiceMedia($locale)
    {
        return $this->service && $this->service->relationLoaded('media')
            ? $this->filterMedia($this->service->media, $locale)
            : [];
    }

    private function filterMedia($mediaCollection, $locale)
    {
        return $mediaCollection->filter(function ($media) use ($locale) {
            return isset($media->custom_properties['language']) &&
                $media->custom_properties['language'] === $locale;
        })->map(function ($media) {
            return collect($media)->only(['id', 'original_url', 'collection_name']);
        })->values();
    }
}
