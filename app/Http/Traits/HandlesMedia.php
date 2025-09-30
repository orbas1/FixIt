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
                    $media = $model->addMedia($file)
                        ->withCustomProperties([
                            'language' => $language,
                            'scan_status' => 'pending',
                        ])
                        ->toMediaCollection($collection);

                    try {
                        app(\App\Services\Security\FileScanService::class)->scan($media);
                    } catch (\Throwable $exception) {
                        $media->setCustomProperty('scan_status', 'failed');
                        $media->setCustomProperty('scan_error', $exception->getMessage());
                        $media->save();
                        report($exception);
                    }
                }
            }
        }
    }
}