<?php

namespace App\Observers;

use App\Enums\BannerTypeEnum;
use App\Models\Advertisement;
use App\Models\Banner;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Banner::where('related_id', $user->id)
              ->where('type', BannerTypeEnum::PROVIDER)
              ->delete();

               Advertisement::where('provider_id', $user->id)
              ->delete();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
