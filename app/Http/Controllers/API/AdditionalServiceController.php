<?php

namespace App\Http\Controllers\API;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateAdditionalServiceRequest;
use App\Http\Requests\API\UpdateAdditionalServiceRequest;
use App\Repositories\API\AdditionalServiceRepository;

class AdditionalServiceController extends Controller
{
    protected $repository;

    public function __construct(AdditionalServiceRepository $repository, Service $service)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->repository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAdditionalServiceRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       return $this->repository->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdditionalServiceRequest $request, string $id)
    {
       return $this->repository->update($request->all(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->repository->destroy($id);
    }
}
