<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateServicePackageRequest;
use App\Http\Requests\API\UpdateServicePackageRequest;
use App\Repositories\API\ServicePackageRepository;
use Illuminate\Http\Request;

class ServicePackageController extends Controller
{
    protected $repository;

    public function __construct(ServicePackageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        //
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
    public function store(CreateServicePackageRequest $request)
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicePackageRequest $request, $servicePackage)
    {
        return $this->repository->update($request, $servicePackage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    function updateStatus(Request $request, $id) 
    {
        return $this->repository->updateStatus($request['status'], $id);
    }
}
