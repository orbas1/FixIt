<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicePackageDetailResource extends BaseResource
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
            'title' => $this->getTranslation('title', $locale),
            'description' => $this->getTranslation('description', $locale),
            'discount' => $this->discount,
            'price' => $this->price,
            'status' => $this->status,
            'hexa_code' => $this->hexa_code,
            'bg_color' => $this->bg_color,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'media' => $this->getMedia('image')
            ->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) &&
                    $media->custom_properties['language'] === $locale;
            })
            ->take(1)
            ->map(function ($media) {
                return collect($media)->only(['id', 'original_url', 'collection_name']);
            }),
            'user' => $this->whenLoaded('user', function () use ($request) {
              $locale = $request->header('Accept-Lang') ?? app()->getLocale();
              return [
                  'id' => $this?->user?->id,
                  'name' => $this->user?->name,
                  'review_ratings' => $this->user?->review_ratings,
                  'media' => $this->user->media->map(function ($media) {
                          return collect($media)->only(['original_url']);
                      }),
              ];
          }),
          
          'services' => $this->services->map(function ($service) use ($request) {
            $locale = $request->header('Accept-Lang') ?? app()->getLocale();
            return [
                'id' => $service->id,
                'title' => $service->title,
                'type' => $service->type,
                'service_rate' => $service->service_rate,
                'price' => $service->price,
                'duration' => $service->duration,
                'duration_unit' => $service->duration_unit,
                'required_servicemen' => $service->required_servicemen,
                'status' => $service->status,
                'media' => $service->getMedia('thumbnail')
                ->filter(function ($media) use ($locale) {
                    return isset($media->custom_properties['language']) &&
                        $media->custom_properties['language'] === $locale;
                })
                ->map(function ($media) {
                    return collect($media)->only(['id', 'original_url', 'collection_name']);
                }),
              ];
            }),
        ];
    }

    /**
     * Filter media based on locale.
     */
    private function getFilteredMedia($locale)
    {
        return $this->whenLoaded('media', function () use ($locale) {
            return $this->media->filter(function ($media) use ($locale) {
                return isset($media->custom_properties['language']) &&
                       $media->custom_properties['language'] === $locale;
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
        }, []);
    }
}
