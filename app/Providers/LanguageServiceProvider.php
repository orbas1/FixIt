<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Lang;

class LanguageServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        // Register custom language directories
        Lang::addNamespace('frontend', resource_path('lang/frontend'));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}
