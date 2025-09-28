<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'description' => $this->description,
            'created_at' => $this->created_at->toDateTimeString(),

            'consumer' => [
                'id' => $this->consumer->id ?? null,
                'name' => $this->consumer->name ?? null,
                'media' => $this->consumer ? $this->consumer->media->map(function($media){
                    return [
                        'original_url' => $media->original_url
                    ];
                }) : [],
            ],

            'serviceman' => [
                'id' => $this->serviceman->id ?? null,
                'name' => $this->serviceman->name ?? null,
                'media' => $this->serviceman ? $this->serviceman->media->map(function($media){
                    return [
                        'original_url' => $media->original_url
                    ];
                }) : [],
            ],

            'provider' => [
                'id' => $this->provider->id ?? null,
                'name' => $this->provider->name ?? null,
                'media' => $this->provider ? $this->provider->media->map(function($media){
                    return [
                        'original_url' => $media->original_url
                    ];
                }) : [],
            ],

            'service' => [
                'id' => $this->service->id ?? null,
                'title' => $this->service->title ?? null,
                'media' => $this->service ? $this->service->getmedia('thumbnail')->take(1)->map(function($media){
                    return [
                        'original_url' => $media->original_url
                    ];
                }) : [],
            ],
        ];
    }
}
