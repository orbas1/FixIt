<?php

namespace App\Observers;

use App\Enums\BannerTypeEnum;
use App\Models\Banner;
use App\Models\Service;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function created(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "updated" event.
     */
    public function updated(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
       // Delete related banners where related_id matches and type is 'service'
       Banner::where('related_id', $service->id)
            ->where('type', BannerTypeEnum::SERVICE)
            ->delete();
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        //
    }
}
