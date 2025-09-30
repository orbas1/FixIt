<?php

namespace App\Providers;

use App\Models\Customer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::prefix('backend')
                ->middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/backend.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('thread-messages', function (Request $request) {
            $identifier = optional($request->user())->id ?: $request->ip();

            return [
                Limit::perMinute(30)->by('thread-minute:' . $identifier),
                Limit::perHour(200)->by('thread-hour:' . $identifier),
            ];
        });

        RateLimiter::for('escrow-funding', function (Request $request) {
            $identifier = optional($request->user())->id ?: $request->ip();

            return [
                Limit::perMinute(5)->by('escrow-minute:' . $identifier),
                Limit::perHour(20)->by('escrow-hour:' . $identifier),
            ];
        });

        RateLimiter::for('service-requests', function (Request $request) {
            $identifier = optional($request->user())->id ?: $request->ip();

            return [
                Limit::perMinute(6)->by('sr-minute:' . $identifier),
                Limit::perHour(30)->by('sr-hour:' . $identifier),
            ];
        });
    }
}
