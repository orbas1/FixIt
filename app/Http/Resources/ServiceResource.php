<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    protected $showSensitiveAttributes = true;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Lang') ?? app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->fetchTranslation('title'),
            'price' => $this->price,
            'status' => $this->status,
            'duration' => $this->duration,
            'duration_unit' => $this->duration_unit,
            'discount' => $this->discount,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'is_featured' => $this->is_featured,
            'is_favourite' => $this->is_favourite,
            'is_favourite_id' => $this->is_favourite_id,
            'is_advertised' => $this->is_advertised,
            'user_id' => $this->user_id,
            'service_rate' => $this->service_rate,
            'required_servicemen' => $this->required_servicemen,
            'service_type' => $this->service_type,
            'bookings_count' => $this->bookings_count,
            'rating_count' => $this->rating_count,
            'media' => $this->getMedia('thumbnail')
                ->filter(function ($media) use ($locale) {
                    return isset($media->custom_properties['language']) &&
                        $media->custom_properties['language'] === $locale;
                })
                ->take(1)
                ->map(function ($media) {
                    return collect($media)->only(['id', 'original_url', 'collection_name']);
                }),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'title'   => $category->title,
                    ];
                });
            }),
            'user' => $this->whenLoaded('user', function () use ($request) {
                return [
                    'id' => $this?->user?->id,
                    'name' => $this->user?->name,
                    'review_ratings' => $this->user?->review_ratings,
                    'experience_interval' => $this->user?->experience_interval,
                    'experience_duration' => $this->user?->experience_duration,
                    'served' => $this->user?->served,
                    'media' => $this->user->media->map(function ($media) {
                        return collect($media)->only(['original_url']);
                    }),
                ];
            }),
            'destination_location' => $this?->destination_location ? $this?->destination_location : null,
        ];
    }

    public function getMediaAttributes()
    {
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();

        return $this->whenLoaded('media', function () use ($locale) {
            return $this->media->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;})
                    ->map(function ($media) {
                    return collect($media)->except([
                                        'model_type',
                                        'model_id',
                                        'uuid',
                                        'file_name',
                                        'manipulations',
                                        'generated_conversions',
                                        'order_column',
                                        'size',
                                        'mime_type',
                                        'disk',
                                        'conversions_disk',
                                        'updated_at',
                                        'preview_url'
                                    ]);})->values();
            }, []);
    }

    public function fetchTranslation($key)
    {
        $translation = $this->getTranslation($key, app()->getLocale());
        $defaultTranslation = $translation ?? $services[$key];

        if (empty($defaultTranslation)) {
            return $this->getDatabaseValue($key);
        }

        return $defaultTranslation;
    }
}
