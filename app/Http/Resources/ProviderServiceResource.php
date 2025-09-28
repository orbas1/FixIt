<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProviderServiceResource extends BaseResource
{
    protected $showSensitiveAttributes = true;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $request->header('Accept-Lang') ?? app()->getLocale()),
            'price' => $this?->price,
            'status' => $this?->status,
            'deleted_at' => $this?->deleted_at,
            'service_rate' => $this?->service_rate,
            'booking_count' => $this?->bookings()?->count() ?? 0,
            'categories' => $this?->categories?->first()?->title ?? null,
            'media' => $this->getMediaAttributes(),
        ];
    }

    public function getMediaAttributes()
    {
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();

        return $this->whenLoaded('media', function () use ($locale) {
            return $this->getMedia('thumbnail')->filter(function ($media) use ($locale) {
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
                                        'preview_url',
                                        'id',
                                        'name',
                                        'responsive_images',
                                        'created_at',
                                        'custom_properties',
                                    ]);})->values();
            }, []);
    }
}
