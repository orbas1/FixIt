<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Lang') ?? app()->getLocale();

        return [
          'id' => $this->id,
          'title' => $this->title,
          'description' => $this->description,
          'created_at' => $this->created_at,
          'content' => $this->content,
          'created_by' => [
              'name' => $this->created_by->name,
          ],
          'categories' => $this->whenLoaded('categories', function () {
              return $this->categories->map(function ($category) {
                  return [
                      'id' => $category->id,
                      'title'   => $category->title,
                  ];
              });
          }),
          'tags' => $this->whenLoaded('tags', function () {
            return $this->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title'   => $category->title,
                ];
            });
          }),
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
    }
}
