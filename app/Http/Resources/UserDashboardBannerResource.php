<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardBannerResource extends JsonResource
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
            'type' => $this->type,
            'related_id' => $this->related_id,
            'media' => $this?->media ? $this?->media->map(function ($media) use ($locale){
                if(isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale){
                    return [
                        'original_url' => $media?->original_url,
                    ];
                }
            })->filter()->values()  : [],
        ];
    }
}
