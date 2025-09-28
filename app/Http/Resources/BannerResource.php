<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class BannerResource extends BaseResource
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
            'title' => $this->title,
            'type' => $this->type,
            'related_id' => $this->related_id,
            'media' => $this->getMediaAttributes(),

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
                    'preview_url','collection_name', 'name', 'size', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images', 'order_column', 'created_at', 'id'
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
