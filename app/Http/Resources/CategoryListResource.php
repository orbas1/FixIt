<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CategoryListResource extends BaseResource
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
            'category_type' => $this->category_type,
            'title' => $this->title,
            'commission' => $this->commission,
            'media' => $this->getMedia('image')
                ->filter(function ($media) use ($locale) {
                    return isset($media->custom_properties['language']) &&
                        $media->custom_properties['language'] === $locale;
                })
                ->take(1)
                ->map(function ($media) {
                    return collect($media)->only(['id', 'original_url', 'collection_name']);
                }),
            'has_sub_categories' => $this->hasSubCategories ? $this->hasSubCategories->map(function($category) use ($locale){
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'media' => $this->getMedia('image')
                            ->filter(function ($media) use ($locale) {
                                return isset($media->custom_properties['language']) &&
                                    $media->custom_properties['language'] === $locale;
                            })
                            ->take(1)
                            ->map(function ($media) {
                                return collect($media)->only(['id', 'original_url', 'collection_name']);
                            }),
                ];
            }) : [],
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
