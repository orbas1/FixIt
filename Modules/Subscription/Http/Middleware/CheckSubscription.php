<?php

namespace Modules\Subscription\Http\Middleware;

use App\Enums\ModuleEnum;
use App\Helpers\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Subscription\Entities\UserSubscription;
use Nwidart\Modules\Facades\Module;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $module = Module::find(ModuleEnum::SUBSCRIPTION);
        if (! is_null($module) && $module?->isEnabled()) {
            if ($user && UserSubscription::hasActiveSubscription($user->id)) {
                $this->verifyPlanLimitExceeded($user);
            }
        }

        return $next($request);
    }

    public function verifyPlanLimitExceeded($user)
    {
        $plan = $user->activeSubscription;
        if (!$plan) {
            $settings = Helpers::getSettings();
        }

        switch (true) {
            case request()->routeIs('service.store'):
                $name = 'service';
                $count = $user->services()->count();
                $limit = $plan ? $plan->max_services : $settings['default_creation_limits']['allowed_max_services'];
                break;

            case request()->routeIs('serviceman.store'):
                $name = 'serviceman';
                $count = $user->servicemans()->count();
                $limit = $plan ? $plan->max_servicemen : $settings['default_creation_limits']['allowed_max_servicemen'];
                break;

            case request()->routeIs('service-package.store'):
                $name = 'service package';
                $count = $user->service_packages()->count();
                $limit = $plan ? $plan->max_service_packages : $settings['default_creation_limits']['allowed_max_services'];
                break;

            case request()->routeIs('address.store'):
                $name = 'address';
                $count = $user->addresses()->count();
                $limit = $plan ? $plan->max_addresses : $settings['default_creation_limits']['allowed_max_services'];
                break;
        }

        if ($this->exceedsLimit($count, $limit)) {
            return response()->json(['error' => "Exceeded maximum {$name} creation limit."], 403);
        }
    }

    private function exceedsLimit($count, $limit)
    {
        return $count >= $limit;
    }
}
