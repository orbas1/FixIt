<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Http\Requests\API\CreateAdvertisementRequest;
use App\Http\Resources\AdvertisementResource;
use Exception;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Repositories\API\AdvertisementRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;


class AdvertisementController extends Controller
{
    public $repository;

    public function __construct(AdvertisementRepository $repository)
    {
        $this->authorizeResource(Advertisement::class, 'advertisement',[
            'except' => ['index','show'],
        ]);
        $this->repository = $repository;
    }


    public function index(Request $request)
    {
        try {

            $cacheKey = 'advertisements_' . md5(json_encode($request->all()));
            $advertisements = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($request) {
                $ads = $this->filter($this->repository, $request);
                return $ads->with('media')->latest('created_at')->simplePaginate($request->paginate ?? $ads->count());
            });

            return AdvertisementResource::collection($advertisements);

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
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
    public function store(CreateAdvertisementRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->repository->show($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        return $this->repository->update($request, $advertisement?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function filter($advertisement, $request)
    {
        if ($request->field && $request->sort) {
            $advertisement = $advertisement->orderBy($request->field, $request->sort);
        }

        if ($request->advertisement_type) {
            $advertisement = $advertisement->where('type', $request?->advertisement_type);
        }

        if (Helpers::getCurrentRoleName() == RoleEnum::PROVIDER) {
          $advertisement = $advertisement->where('provider_id', Helpers::getCurrentUserId());
        }

        if ($request->app_screen) {
          $advertisement = $advertisement->where('screen', $request?->app_screen);
        }

        if ($request->zone_id) {
            $advertisement = $advertisement->where('zone', $request?->zone_id);
          }

        if (isset($request->status)) {
            $advertisement = $advertisement->where('status', $request->status);
        }

        return $advertisement;
    }
}
