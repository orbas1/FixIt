<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardHomeBannerResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Lang') ?? app()->getLocale();

        return [
            'id' => $this?->id,
            'banner_type' => $this?->banner_type,
            'video_link' => $this?->video_link,
            'media' => $this?->getFilteredMedia($locale),
            'advertisement_type' => $this?->type,
            'advertisement_screen' => $this?->screen,
            'provider_id' => $this?->provider_id
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
