<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProviderSiteServiceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateProviderSiteServiceRequest;
use App\Http\Requests\Backend\UpdateProviderSiteServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Repositories\Backend\ProviderSiteServiceRepository;

class ProviderSiteServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ProviderSiteServiceRepository $repository)
    {
        $this->authorizeResource(Service::class, 'providerSiteService');
        $this->repository = $repository;
    }

    public function index(ProviderSiteServiceDataTable $dataTable)
    {
        return $dataTable->render('backend.providerSiteService.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateProviderSiteServiceRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $tax)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $providerSiteService)
    {
        return $this->repository->edit($providerSiteService?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProviderSiteServiceRequest $request, Service $providerSiteService)
    {
        return $this->repository->update($request, $providerSiteService?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $providerSiteService)
    {
        return $this->repository->destroy($providerSiteService?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }
}
