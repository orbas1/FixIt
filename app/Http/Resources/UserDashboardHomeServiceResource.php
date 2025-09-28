<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardHomeServiceResource extends JsonResource
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
          'provider_id' => $this?->provider_id,
          'services' => $this->services ? $this->services->map( function ($service) use($locale){
                    return [
                      'id' => $service->id,
                      'duration' => $service->duration,
                      'duration_unit' => $service->duration_unit,
                      'required_servicemen' => $service->required_servicemen,
                      'title' => $service->getTranslation('title', $locale),
                      'discount' => $service->discount,
                      'price' => $service->price,
                      'service_rate' => $service->service_rate,
                      'media' => $this->getServiceMediaAttributes($service),
                  ];
          }) : null,
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

    public function getServiceMediaAttributes($service)
    {
        $locale = request()->header('Accept-Lang') ?: app()->getLocale();

            return $service->media->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;
            })->map(function ($media) {
               return collect($media)->only(['original_url', 'id', 'collection_name']);
            })->values();
    }
}
