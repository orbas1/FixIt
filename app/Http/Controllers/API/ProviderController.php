<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Repositories\API\ProviderRepository;
use Exception;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    protected $category;

    protected $service;

    protected $user;

    public function __construct(ProviderRepository $repository, Category $category, Service $service, User $user)
    {
        $this->repository = $repository;
        $this->category = $category;
        $this->service = $service;
        $this->user = $user;
    }

    public function index(Request $request)
    {
        try {
            $providers = $this->repository->role('provider')->with(['addresses', 'servicemans', 'media']);
            if ($request->ids) {
                $ids = explode(',', $request->ids);
                $providers->WhereIn('id', $ids);
            }

            if ($request->category_ids) {
                $category_ids = explode(',', $request->category_ids);
                $providers->whereHas('services.categories', function ($query) use ($category_ids) {
                    $query->whereIn('categories.id', $category_ids);
                });
            }

            if ($request->type) {
                $providers = $providers->whereNull('parent_id')->where('category_type', 'like', $request->type)->with(['subcategories']);
            }

            if ($request->experience) {
                $experienceCriteria = $request->experience;

                $providers = User::query()->role('provider');

                if ($experienceCriteria === 'low') {
                    $providers->orderByRaw('CASE WHEN experience_interval = "months" THEN 1 ELSE 2 END ASC')
                        ->orderBy('experience_duration', 'asc');
                } elseif ($experienceCriteria === 'high') {
                    $providers->orderByRaw('CASE WHEN experience_interval = "years" THEN 1 ELSE 2 END ASC')
                        ->orderBy('experience_duration', 'desc');
                }
            }

            if ($request->served === 'low') {
                $providers = $providers->orderBy('served', 'asc');
            }

            if ($request->served === 'high') {
                $providers = $providers->orderBy('served', 'desc');
            }

            if ($request->search) {
                $providers = $providers->where('name', 'like', '%'.$request->search.'%');
            }
            return ProviderResource::collection(
                $providers->latest('created_at')->paginate($request->paginate ?? $providers->count())
            );
            // return $providers->latest('created_at')->paginate($request->paginate ?? $providers->count());
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProviderRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->repository->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function isValidTimeSlot(Request $request)
    {
        return $this->repository->isValidTimeSlot($request);
    }

    public function providerTimeSlot(Request $request)
    {
        return $this->repository->providerTimeslot($request->provider_id);
    }

    public function storeProviderTimeSlot(Request $request)
    {
        return $this->repository->storeProviderTimeSlot($request);
    }

    public function updateProviderTimeSlot(Request $request)
    {
        return $this->repository->updateProviderTimeSlot($request);
    }

    public function updateTimeSlotStatus(Request $request, $timeslotID)
    {
        return $this->repository->updateTimeSlotStatus($request->status, $timeslotID);
    }

    public function getUsersWithHighestRatings(Request $request)
    {
        return $this->repository->getUsersWithHighestRatings($request);
    }

    public function getProviderServices(Request $request)
    {
        return $this->repository->getProviderServices($request);
    }

    public function updateProviderZones(Request $request)
    {
        return $this->repository->updateProviderZones($request);
    }

    public function updateCompanyDetails(Request $request)
    {
        return $this->repository->updateCompanyDetails($request);
    }

    public function getDashboardData()
    {
        return $this->repository->getDashboardData();
    }
}
