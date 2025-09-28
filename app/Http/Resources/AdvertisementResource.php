<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AdvertisementResource extends BaseResource
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
            'provider_id' => $this->provider_id,
            'type' => $this->type,
            'banner_type' => $this->banner_type,
            'screen' => $this->screen,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'media' => $this->getMediaAttributes(),
            'services' => $this->getServicesAttributes($request),
            'video_link' => $this->video_link,
            'zone' => $this->zone_id->name
        ];
    }

    public function getMediaAttributes()
    {
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();

        return $this->whenLoaded('media', function () use ($locale) {
            return $this->media->filter(function ($media) use ($locale) {

                return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;})
                    ->map(function ($media) {
                    return collect($media)->only(['original_url']);
                })->values();
            }, []);
    }

    public function getServicesAttributes($request)
    {
        if ($this->services) {

            return $this->services->map(function ($service) use ($request) {
                $locale = $request->header('Accept-Lang') ?? app()->getLocale();
                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'price' => $service->price,
                    'discount' => $service->discount,
                    'required_servicemen' => $service->required_servicemen,
                    'service_rate' => $service->service_rate,
                    'media' => $service->getMedia('thumbnail')
                    ->filter(function ($media) use ($locale) {
                        return isset($media->custom_properties['language']) &&
                            $media->custom_properties['language'] === $locale;
                    })
                    ->take(1)
                    ->map(function ($media) {
                        return collect($media)->only(['id', 'original_url', 'collection_name']);
                    }),
                  ];
            });
        }

        return [];
    }

    public function getServiceMediaAttributes($service)
    {
        $locale = request()->header('Accept-Lang') ?: app()->getLocale();

            return $service->media->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;
            })->map(function ($media) {
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
                ]);
            })->values();
    }


    public function fetchTranslation($key)
    {
        $translation = $this->getTranslation($key, app()->getLocale());
        $defaultTranslation = $translation ?? $this[$key];

        if (empty($defaultTranslation)) {
            return $this->getDatabaseValue($key);
        }

        return $defaultTranslation;
    }

    public function getDatabaseValue($key)
    {
        return $this->getRawOriginal($key);
    }
}
