<?php

namespace App\Providers;

use App\Helpers\Helpers;
use App\Models\Bid;
use App\Models\ServiceRequest;
use App\Observers\ServiceRequestObserver;
use App\Observers\BidObserver;
use App\Services\Compliance\ComplianceReporter;
use App\Services\Dispute\DisputeService;
use App\Services\Escrow\EscrowLedgerService;
use App\Services\Escrow\EscrowService;
use App\Services\Escrow\Gateways\EscrowGateway;
use App\Services\Escrow\Gateways\InMemoryEscrowGateway;
use App\Services\Escrow\Gateways\StripeEscrowGateway;
use App\Services\Guardian;
use App\Services\Geo\IpLocationResolver;
use App\Services\Media\ImageVariantService;
use App\Services\Media\ImgproxyUrlBuilder;
use App\Services\Security\ContentGuardService;
use App\Services\Security\FileScanService;
use App\Services\Security\LinkRedirectService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
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
use RuntimeException;
use Laravel\Dusk\DuskServiceProvider;
use Spatie\Translatable\Facades\Translatable;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        $this->app->singleton(ComplianceReporter::class);
        $this->app->singleton(EscrowLedgerService::class);
        $this->app->singleton(LinkRedirectService::class);
        $this->app->singleton(ContentGuardService::class);

        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient((string) config('services.stripe.secret'));
        });

        $this->app->bind(EscrowGateway::class, function ($app) {
            $gateway = config('escrow.gateway', 'stripe');

            return match ($gateway) {
                'stripe' => new StripeEscrowGateway($app->make(StripeClient::class)),
                'in-memory', 'array', 'testing' => new InMemoryEscrowGateway(),
                default => throw new RuntimeException("Unsupported escrow gateway [{$gateway}]."),
            };
        });

        $this->app->singleton(EscrowService::class, function ($app) {
            return new EscrowService(
                $app->make(EscrowGateway::class),
                $app->make(EscrowLedgerService::class),
                $app->make(ComplianceReporter::class),
            );
        });

        $this->app->singleton(DisputeService::class, function ($app) {
            return new DisputeService(
                $app->make(EscrowService::class),
                $app->make(ComplianceReporter::class),
            );
        });

        $this->app->singleton(FileScanService::class);

        $this->app->singleton(ImgproxyUrlBuilder::class, function ($app) {
            $config = $app['config']->get('media.imgproxy', []);

            return new ImgproxyUrlBuilder(
                $config['endpoint'] ?? null,
                $config['key'] ?? null,
                $config['salt'] ?? null,
                (bool) ($config['enabled'] ?? false),
                $config['format'] ?? 'webp',
                (int) ($config['quality'] ?? 82),
            );
        });

        $this->app->singleton(ImageVariantService::class);

        if (! $this->app->bound(ClientInterface::class)) {
            $this->app->bind(ClientInterface::class, function () {
                return new Client([
                    'timeout' => 5,
                    'connect_timeout' => 5,
                ]);
            });
        }

        $this->app->singleton(IpLocationResolver::class, function ($app) {
            return new IpLocationResolver($app->make(ClientInterface::class));
        });
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
        Model::preventLazyLoading(! $this->app->isProduction());

        ServiceRequest::observe(ServiceRequestObserver::class);
        Bid::observe(BidObserver::class);
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
