<?php

namespace App\Jobs\Security;

use App\Services\Security\FileScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ScanMedia implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public readonly int $mediaId)
    {
        $this->onQueue('media');
    }

    public function uniqueId(): string
    {
        return (string) $this->mediaId;
    }

    public function handle(FileScanService $scanner): void
    {
        /** @var Media|null $media */
        $media = Media::query()->find($this->mediaId);
        if (! $media) {
            return;
        }

        try {
            $scanner->scan($media);
        } catch (\Throwable $exception) {
            $media->setCustomProperty('scan_status', 'failed');
            $media->setCustomProperty('scan_error', $exception->getMessage());
            $media->save();

            throw $exception;
        }
    }
}
