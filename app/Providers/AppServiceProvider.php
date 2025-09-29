<?php

namespace App\Providers;

use App\Helpers\Helpers;
use App\Models\ServiceRequest;
use App\Observers\ServiceRequestObserver;
use App\Services\Guardian;
use Database\Seeders\ThemeOptionSeeder;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Guardian::bootApplication();
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Collection::macro('paginate', function ($perPage = 15) {
            $page = LengthAwarePaginator::resolveCurrentPage('page');
            return new LengthAwarePaginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]);
        });

        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        if(!request()->wantsJson()){
            $themeOptions = $this->getThemeOptions();
            view()->share('themeOptions', $themeOptions);
        }
        Translatable::fallback(fallbackAny: true);
        Model::automaticallyEagerLoadRelationships();

        ServiceRequest::observe(ServiceRequestObserver::class);
    }

    private function getThemeOptions()
    {
        if ($this->isDatabaseConnected()) {
            try {
                return  Helpers::getThemeOptions();

            } catch (Exception $e) {
                return $this->getDefaultThemeOptions();
            }
        }

        return $this->getDefaultThemeOptions();
    }

    private function isDatabaseConnected()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function databaseHasTables()
    {
        try {
            return count(Schema::getAllTables()) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getDefaultThemeOptions()
    {
        $themeOptionsSeeder = new ThemeOptionSeeder();
        return $themeOptionsSeeder->getThemeOptions();
    }
}
