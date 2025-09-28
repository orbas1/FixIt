<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'date_time' => $this->date_time,
            'required_servicemen' => $this->required_servicemen,
            'parent_booking_number' => $this?->parent?->booking_number, 
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'grand_total_with_extras' => $this->grand_total_with_extras,
            'date_time' => $this->date_time,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'booking_status' => $this->whenLoaded('booking_status'),
            'service' => $this->whenLoaded('service', function () use ($request) {
                $locale = $request->header('Accept-Lang') ?? app()->getLocale();
                return [
                    'title' => $this?->service?->getTranslation('title', $locale),
                    'price' => $this?->service?->price,
                    'service_rate' => $this?->service_rate,
                    'media' => $this?->service?->getMedia('thumbnail')->take(1)->map(function ($media) {
                        return collect($media)->only(['id', 'original_url', 'collection_name']);
                    }),
                ];
            }),
            'consumer' => [
                'name' => $this?->consumer?->name,
                'media' => $this?->consumer?->media ? $this?->consumer?->media?->map(function($media){
                    return [
                        'original_url' => $media?->original_url,
                    ];
                }) : [],
            ],
            'provider' => [
                'name' => $this->provider?->name,
                'role' => $this->provider?->role?->name,
                'review_ratings' => $this->provider?->review_ratings,
                'media' => $this?->provider?->media->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
            ],
            'servicemen' => $this->servicemen->map(function ($serviceman) {
                return [
                    'name' => $serviceman->name,
                    'role' => $serviceman->role?->name,
                    'review_ratings' => $serviceman->review_ratings,
                    'media' => $serviceman?->media?->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
                ];
            }),
            'address' => $this->whenLoaded('address') ? [
                'area' => $this?->address?->area,
                'address' => $this?->address?->address,
                'postal_code' => $this?->address?->postal_code,
                'latitude' => $this?->address?->latitude,
                'longitude' => $this?->address?->longitude,
                'country' => [
                    'id' => $this?->address?->country?->id,
                    'name' => $this?->address?->country?->name
                ],
                'state' => [
                    'id' => $this?->address?->country?->id,
                    'name' => $this?->address?->country?->name,
                ]
            ] : null,
            'extra_charges' => $this?->extra_charges ? $this?->extra_charges->map(function($extraCharges){
                return [
                    'id' => $extraCharges->id,
                    'title' => $extraCharges->title,
                    'per_service_amount' => $extraCharges->per_service_amount,
                    'no_service_done' => $extraCharges->no_service_done,
                    'payment_method' => $extraCharges->payment_method,
                    'payment_status' => $extraCharges->payment_status,
                    'total' => $extraCharges->total,
                ];
            }) : [],
        ];
    }
}