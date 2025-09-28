<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateRequestServiceRequest;
use App\Http\Resources\serviceRequestResource;
use App\Repositories\API\ServiceRequestRepository;

class ServiceRequestController extends Controller
{
    public $repository;

    public function  __construct(ServiceRequestRepository $repository)
    {
        $this->authorizeResource(ServiceRequest::class, 'serviceRequest');
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $serviceRequests = $this->repository->whereNull('deleted_at');
        $serviceRequests = $this->filter($serviceRequests, $request);
        $serviceRequests = $serviceRequests->latest('created_at')->simplePaginate($request->paginate ?? $serviceRequests->count());
        return serviceRequestResource::collection($serviceRequests ?? []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequestServiceRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        return $this->repository->show($serviceRequest->id);
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
    public function update(Request $request, string $id)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        return $this->repository->destroy($serviceRequest->id);
    }

    public function filter($serviceRequests, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName == RoleEnum::CONSUMER) {
            $serviceRequests = $serviceRequests->where('user_id', Helpers::getCurrentUserId());
        }

        return $serviceRequests->with(['media', 'bids', 'service.user']);
    }
}
