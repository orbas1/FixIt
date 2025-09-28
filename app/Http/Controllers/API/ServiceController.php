<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Service;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Models\ServicePackage;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\API\ServiceRepository;
use App\Http\Resources\ServiceDetailResource;
use App\Http\Requests\API\CreateServiceRequest;
use App\Http\Requests\API\UpdateServiceRequest;
use App\Http\Resources\FeaturedServiceResource;
use App\Http\Resources\ServicePackageResource;
use App\Http\Requests\API\StoreServiceAddressRequest;
use App\Http\Resources\UserDashboardFeaturedServiceResource;

class ServiceController extends Controller
{
    public $repository;

    public $servicePackage;

    public $model;

    public function __construct(ServiceRepository $repository, Service $service, ServicePackage $servicePackage)
    {
        $this->authorizeResource(Service::class, 'service', [
            'except' => ['index', 'show'],
        ]);
        $this->model = $service;
        $this->repository = $repository;
        $this->servicePackage = $servicePackage;
    }

    public function index(Request $request)
    {
        if ($request->serviceId) {
            $service = Service::with('user', 'additionalServices.media')->findOrFail($request->serviceId);
            return response()->json([
                'success' => true,
                'data' =>  new ServiceDetailResource($service),
            ]);
        }

        $query = Service::query()->whereNull('parent_id')->where('status', true)->with(['user', 'user.media'])->orderByDesc('is_advertised')->latest('created_at');

        if ($request->filled('zone_ids')) {
            $zoneIds = explode(',', $request->zone_ids);
            $query->whereHas('categories.zones', function ($q) use ($zoneIds) {
                $q->whereIn('zones.id', $zoneIds);
            });
        }

        if ($request->filled('popular_service')) {
            $query->withCount('bookings')->orderByDesc('bookings_count');
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status') && !$request->filled('search')) {
            $query->where('status', true);
        }

        if ($request->filled('categoryIds')) {
            $categoryIds = explode(',', $request->categoryIds);
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        }

        if ($request->filled('rating')) {
            $rating = explode(',', $request->rating);
            $query = $this->getServiceByRating($rating, $query);
        }

        if ($request->filled('providerIds')) {
            $providerIds = explode(',', $request->providerIds);
            $query->whereIn('user_id', $providerIds);
        }

        if ($request->filled('min') && $request->filled('max')) {
            $query->whereBetween('price', [$request->min, $request->max]);
        }

        $services = $query->paginate($request->paginate ?? $query->count()); 

        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection($services),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateServiceRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        $service = Service::query()->whereNull('parent_id')->with(['user' , 'user.media','categories:title,id' ,'faqs','related_services'])->where('id' , $service->id)->first();
        if($service){
            return response()->json([
                'success' => true,
                'data' =>  new ServiceDetailResource($service),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __('static.service.service_not_found')
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        return $this->repository->edit($service->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        return $this->repository->update($request->all(), $service->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        return $this->repository->destroy($service?->id);
    }

    public function isFeatured(Request $request)
    {
        $query = $this->model->query()
            ->where('is_featured', true)
            ->with('user.media') 
            ->latest('created_at');

        if ($request->filled('zone_ids')) {
            $zone_ids = explode(',', $request->zone_ids);

            $query->whereHas('categories.zones', function (Builder $zones) use ($zone_ids) {
                $zones->whereIn('zones.id', $zone_ids);
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('paginate', $query->count()); 
        $paginated = $query->simplePaginate($perPage);

        return FeaturedServiceResource::collection($paginated);
    }

    public function servicePackages(Request $request)
    {
        try {

            if (Helpers::isUserLogin()) {
                $roleName = Helpers::getCurrentRoleName();
                if ($roleName == RoleEnum::PROVIDER) {
                    $servicePackage = $this->servicePackage->where('provider_id', Helpers::getCurrentProviderId());
                } else {
                    $servicePackage = $this->servicePackage->where('status', true)->withCount('services')->having('services_count', '>=', 2);
                }
            } else {
                $servicePackage = $this->servicePackage->where('status', true)->withCount('services')->having('services_count', '>=', 2);
            }

            if ($request->id) {
                $servicePackage->where('id', $request->id);
            }

            if ($request->zone_ids) {
                $zone_ids = explode(',', $request->zone_ids);
                $servicePackage = $servicePackage->whereHas('services', function (Builder $services) use ($zone_ids) {
                    $services->whereHas('categories', function (Builder $categories) use ($zone_ids) {
                        $categories->whereHas('zones', function (Builder $zones) use ($zone_ids) {
                            $zones->WhereIn('zones.id', $zone_ids);
                        });
                    });
                });
            }

            $paginate = $request->input('paginate', $servicePackage->count());

            return ServicePackageResource::collection($servicePackage->latest('created_at')->simplePaginate($paginate));

        } catch (Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeServiceAddresses(StoreServiceAddressRequest $request, $id)
    {
        return $this->repository->storeServiceAddresses($request, $id);
    }

    public function deleteServiceAddresses($id, $address_id)
    {
        return $this->repository->deleteServiceAddresses($id, $address_id);
    }

    public function serviceFAQS(Request $request)
    {
        return $this->repository->serviceFAQS($request);
    }

    public function getServiceByRating($ratings, $services)
    {
        return $services->where(function ($query) use ($ratings) {
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
    }

    public function showCustomOffer(Request $request)
    {
        $request->validate([
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $service = Service::withoutGlobalScopes()  
            ->where('is_custom_offer', true)       
            ->with(['categories', 'taxes', 'user', 'reviews'])
            ->findOrFail($request->service_id);

        return response()->json([
            'status' => true,
            'message' => 'Custom offer service fetched successfully',
            'data' => new UserDashboardFeaturedServiceResource($service),
        ]);
    }
}
