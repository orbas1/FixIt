<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardCategoryResource extends JsonResource
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
            'title' => $this?->title,
            'parent_id' => $this?->parent_id,
            'media' => $this?->getFilteredMedia($locale),
            'hasSubCategories' => $this->hasSubCategories->map(function ($subCategory) use ($locale) {
                return [
                    'id' => $subCategory->id,
                    'title' => $subCategory->getTranslation('title', $locale),
                    'parent_id' => $subCategory->parent_id,
                    'media' => $this->filterMedia($subCategory, $locale),
                ];
            }),
        ];
    }

    private function getFilteredMedia($locale)
    {
        return $this->whenLoaded('media', function () use ($locale) {
            return $this->media->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) &&
                    $media->custom_properties['language'] === $locale;
            })->map(function ($media) {
                return collect($media)->only(['original_url','id']);
            })->values();
        }, []);
    }

    private function filterMedia($model, $locale)
    {
        return $model->relationLoaded('media')
            ? $model->media->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;
            })->map(function ($media) {
                return collect($media)->only(['original_url']);
            })->values()
            : [];
    }
}
