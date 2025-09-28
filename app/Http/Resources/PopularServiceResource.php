<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopularServiceResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'status' => $this->status,
            'rating_count' => $this?->rating_count ?? null,
            'bookings_count' => $this?->bookings_count ?? null,
            'categories' => $this?->categories?->first()?->title ?? null, 
            'media' => $this->getFilteredMedia($locale),
        ];
    }

    public function getFilteredMedia($locale)
    {
        return $this->getMedia('thumbnail')->take(1)->filter(function ($media) use ($locale) {
            return isset($media->custom_properties['language']) &&
                $media->custom_properties['language'] === $locale;
        })->map(function ($media) {
            return collect($media)->except([
                'model_type', 'model_id', 'uuid', 'file_name', 'manipulations',
                'generated_conversions', 'order_column', 'size', 'mime_type',
                'disk', 'conversions_disk', 'updated_at', 'preview_url', 'responsive_images' ,'name', 'created_at'
            ]);
        })->values();
    }
}
