<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->provider_id && $this->provider) {
            return [
                'id' => $this->id,
                'provider' => [
                    'id' => $this->provider->id,
                    'name' => $this->provider->name,
                    'review_ratings' => $this->provider->review_ratings,
                    'media' => $this->provider->media ? $this->provider->media->map(function ($media) {
                        return [
                            'original_url' => $media->original_url,
                        ];
                    }) : [],
                ],
            ];
        }
        if ($this->service_id && $this->service) {
            return [
                'id' => $this->id,
                'service' => [
                    'id' => $this->service->id,
                    'duration' => $this->service->duration,
                    'duration_unit' => $this->service->duration_unit,
                    'required_servicemen' => $this->service->required_servicemen,
                    'title' => $this->service->title,
                    'discount' => $this->service->discount,
                    'price' => $this->service->price,
                    'service_rate' => $this->service->service_rate,
                    'type' => $this->service->type,
                    'is_favourite' => $this->service->is_favourite,
                    'categories' => $this->service->categories ? $this->service->categories->take(1)->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'title' => $category->title,
                        ];
                    }) : [],
                    'media' => $this->service->getMedia('thumbnail') ? $this->service->getMedia('thumbnail')->take(1)->map(function ($media) {
                        return [
                            'original_url' => $media->original_url,
                        ];
                    }) : [],
                ],
            ];
        }

        return [];
    }
}
