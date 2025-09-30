<?php

namespace App\Services\Media;

use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageVariantService
{
    public function __construct(private readonly ImgproxyUrlBuilder $urlBuilder)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(Media $media): array
    {
        $variants = [];
        $configVariants = config('media.variants', []);

        foreach ($configVariants as $name => $options) {
            $variants[$name] = [
                'url' => $this->urlBuilder->make($media->getFullUrl(), $options),
                'width' => (int) Arr::get($options, 'width', 0),
                'height' => (int) Arr::get($options, 'height', 0),
                'format' => $options['format'] ?? config('media.imgproxy.format'),
                'quality' => (int) ($options['quality'] ?? config('media.imgproxy.quality')),
            ];
        }

        return [
            'id' => $media->id,
            'name' => $media->name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'blurhash' => $media->getCustomProperty('blurhash'),
            'original_url' => $media->getFullUrl(),
            'variants' => $variants,
        ];
    }
}
