<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ZoneResource extends BaseResource
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
            'name' => $this->name,
            'locations' => $this->locations,
            'payment_methods' => $this->payment_methods,
            'status' => $this->status,
            'currency' => $this->whenLoaded('currency', function () use ($request) {
              return [
                  'id' => $this?->currency?->id,
                  'code' => $this->currency?->code,
                  'symbol' => $this->currency?->symbol,
                  'no_of_decimal' => $this->currency?->no_of_decimal,
                  'exchange_rate' => $this->currency?->exchange_rate,
                  'status' => $this->currency?->status,
              ];
          }),
        ];
    }

    public function getMediaAttributes()
    {
        if ($this->media) {
            return $this->media->map(function ($media) {
                return collect($media)->except([
                    'model_type',
                    'model_id',
                    'uuid',
                    'file_name',
                    'mime_type',
                    'disk',
                    'conversions_disk',
                    'updated_at',
                    'preview_url'
                ]);
            });
        }
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
