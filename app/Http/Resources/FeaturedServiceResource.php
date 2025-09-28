<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedServiceResource extends JsonResource
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
            'duration' => $this?->duration,
            'duration_unit' => $this?->duration_unit,
            'required_servicemen' => $this?->required_servicemen,
            'title' => $this?->getTranslation('title', $locale),
            'discount' => $this?->discount,
            'discount' => $this?->discount,
            'price' => $this?->price,
            'service_rate' => $this?->service_rate,
            'type' => $this?->type,
            'media' => $this->getMedia('thumbnail')
            ->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) &&
                    $media->custom_properties['language'] === $locale;
            })
            ->take(1)
            ->map(function ($media) {
                return collect($media)->only(['original_url']);
            }),
            'status' => $this->status,
            'user' => $this->whenLoaded('user', function ($user) use ($locale) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'review_ratings' => $user->review_ratings,
                    'media' => $this->getUserMedia($user)
                ];
            }),
        ];
    }
    
    private function getUserMedia($user)
    {
        return $user->relationLoaded('media')
            ? $user->media->map(function ($media) {
                return collect($media)->only(['original_url']);
            })->values()
            : [];
    }
}
