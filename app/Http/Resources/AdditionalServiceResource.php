<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
        return [
            'id'         => $this->id,
            'title'      => $this->getTranslation('title', $locale),
            'price'      => $this->price,
            'parent_id'  => $this->parent_id,
            'user_id'    => $this->user_id,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'media'      => $this?->media->filter(function ($media) use ($locale) {
                                return isset($media->custom_properties['language']) &&
                                    $media->custom_properties['language'] === $locale;
                            })->map(function ($media) {
                                return collect($media)->only(['original_url', 'collection_name', 'id']);
                            })
        ];
    }
}
