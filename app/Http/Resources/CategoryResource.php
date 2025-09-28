<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CategoryResource extends BaseResource
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
            'title' => $this->title,
            'commission' => $this->commission,
            'services_count' => $this->services_count,
            'is_child' => $this->hasSubCategories()->exists(),
            'media' => $this->getMedia('image')
                ->filter(function ($media) use ($locale) {
                    return isset($media->custom_properties['language']) &&
                        $media->custom_properties['language'] === $locale;
                })
                ->take(1)
                ->map(function ($media) {
                    return collect($media)->only(['id', 'original_url', 'collection_name']);
                })->values(),
            'services' => ServiceResource::collection($this->whenLoaded('services'))
        ];
    }

    /**
     * Filter media attributes.
     */
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
}
