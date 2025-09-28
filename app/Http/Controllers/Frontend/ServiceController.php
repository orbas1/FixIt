<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helpers;
use App\Enums\BookingEnum;
use App\Enums\ServiceTypeEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Frontend\ServiceRepository;

class ServiceController extends Controller
{
    public $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $services = $this->filter($this->repository->whereNull('parent_id'), $request);
        $services = $services->where('is_custom_offer', false);
        $providers = $this->getProviderByServices($services) ;
        if($request->provider_sortBy)
        {
            $services = $services->where('status', 1)->paginate(Helpers::getThemeOptions()['pagination']['service_list_per_page']);
        }else{
            $services = $services->where('status', 1)->latest()?->paginate(Helpers::getThemeOptions()['pagination']['service_list_per_page']);
        }
        return view('frontend.service.index', ['services' => $services?->withQueryString(), 'providers' => $providers]);
    }

    public function details($slug)
    {
        return $this->repository->details($slug);
    }

    public function search(Request $request)
    {
        return $this->repository->search($request);
    }

    public function getProviderByServices($services)
    {
        $providerIds = $services?->pluck('user_id')?->toArray();
        return User::role('provider')?->whereIn('id', $providerIds ?? []);
    }

    public function filter($services, $request)
    {
        $zoneIds = session('zoneIds', []);
        $services = $services?->whereHas('categories', function (Builder $categories) use ($zoneIds) {
            $categories->whereHas('zones', function (Builder $zones) use ($zoneIds) {
                $zones->WhereIn('zones.id', $zoneIds);
            });
        });

        if ($request->search) {
            $services = $services->where('title', 'like', '%'.$request->search.'%')
            ->whereNull('deleted_at');
        }

        if ($request->price) {
            $prices =explode(';', $request->price);
            $services = $services->whereBetween('service_rate', [min($prices),max($prices)]);
        }

        if ($request->provider) {
            $providerIds = explode(',', $request->provider);
            $services = $services->whereHas('user', function ($user) use ($providerIds) {
                $user->whereIn('id', $providerIds);
            });
        }

        if ($request->categories) {
            $categorySlugs = explode(',', $request->categories);
            $services = $services->whereHas('categories', function ($categories) use ($categorySlugs) {
                $categories->whereIn('slug', $categorySlugs);
            });
        }

        if ($request->provider_sortBy) {
            if ($request->provider_sortBy == 'high-exp') {
                return $services->get()->sortByDesc(function ($service) {
                    return $service->user->total_days_experience; 
                });
            }

            if ($request->provider_sortBy == 'low-exp') {
                return $services->get()->sortBy(function ($service) {
                    return $service->user->total_days_experience; 
                });
            }

            if ($request->provider_sortBy == 'high-serv') {
                return $services->get()->sortByDesc(function ($service) {
                        return $service->user->served; 
                    });
            }

            if ($request->provider_sortBy == 'low-serv') {
                return $services->get()->sortBy(function ($service) {
                    return $service->user->served; 
                });
            }

            if ($request->rating) {
                $rating = explode(',', $request->rating);
                $services = $this->getServiceByRating($rating, $services);
            }

        }

        return $services;
    }

    public function getServiceByRating($ratings, $services)
    {
        return $services->where(function ($query) use ($ratings) {
            $query->where(function ($query) use ($ratings) {
                foreach ($ratings as $rating) {
                    $query->orWhere(function ($query) use ($rating) {
                        $query->whereHas('reviews', function ($query) use ($rating) {
                            $query->select('service_id')
                                ->groupBy('service_id')
                                ->havingRaw('AVG(rating) >= ?', [$rating])
                                ->havingRaw('AVG(rating) < ?', [$rating + 1]);
                        });
                    });
                }
            });
        });
    }
}
