<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Models
use App\Models\Service;
use App\Models\Category;
use App\Models\User;
// Observers
use App\Observers\ServiceObserver;
use App\Observers\CategoryObserver;
use App\Observers\UserObserver;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Service::observe(ServiceObserver::class);
        Category::observe(CategoryObserver::class);
    }
}
