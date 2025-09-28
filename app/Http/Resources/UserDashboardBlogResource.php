<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardBlogResource extends JsonResource
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
            'title' => $this?->getTranslation('title', $locale),
            'description' => $this?->getTranslation('description', $locale),
            'created_at' => $this?->created_at,
            'created_by' => $this->whenLoaded('created_by', function () {
                return [
                    'id' => $this->created_by->id,
                    'name' => $this->created_by->name,
                ];
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(function ($tag) {
                    return collect($tag)->only(['id', 'name', 'type']);
                });
            }),
            'media' => $this?->getFilteredMedia($locale),
            'categories' => $this?->categories->map(function ($category) {
                return collect($category)->only(['id', 'title']);
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
                return collect($media)->only(['original_url', 'id']);
            })->values();
        }, []);
    }
}
