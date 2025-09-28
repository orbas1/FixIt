<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;

trait HandlesMedia
{
    /**
     * Handle uploading single or multiple images to a media collection with optional custom properties.
     *
     * @param mixed $files Single file or array of files
     * @param string $collection Media collection name
     * @param string|null $language Optional language property for the media
     * @param bool $clearExisting Whether to clear existing media for the specific language
     */

    public function handleMediaUpload($files, $collection, $language, $clearExisting, $model)
    {
        // Clear existing media for the specific language if requested.
        if ($clearExisting && $language) {
            $model->getMedia($collection)
                ->where('custom_properties.language', $language)
                ->each(function ($media) {
                    $media->delete();
                });
        }

        if($files && $files->isNotEmpty()){
            foreach ($files as $file) {
                if ($file->isValid()) {
                    // Add media with custom properties (e.g., language and original filename).
                    $model->addMedia($file)
                        ->withCustomProperties([
                            'language' => $language,
                        ])
                        ->toMediaCollection($collection);
                }
            }
        }
    }
}