<?php

namespace App\Http\Controllers\Backend;

use App\Models\ServicePackage;
use App\DataTables\ServicePackageDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateServicePackageRequest;
use App\Http\Requests\Backend\UpdateServicePackageRequest;
use App\Repositories\Backend\ServicePackageRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class ServicePackageController extends Controller
{
    public $repository;

    public function __construct(ServicePackageRepository $repository)
    {
        $this->authorizeResource(ServicePackage::class, 'service_package');

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ServicePackageDataTable $dataTable)
    {
        return $dataTable->render('backend.service-package.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateServicePackageRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(ServicePackage $service_package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit(ServicePackage $service_package)
    {
        return $this->repository->edit($service_package?->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateServicePackageRequest $request, ServicePackage $service_package)
    {
        return $this->repository->update($request, $service_package?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy(ServicePackage $service_package)
    {
        return $this->repository->destroy($service_package?->id);
    }

    public function getProviderServices(Request $request)
    {
        return $this->repository->getProviderServices($request);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $servicePackage = ServicePackage::find($request->id[$row]);
                $servicePackage->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function export(Request $request)
    {
        return $this->repository->export($request);
    }
    public function import(Request $request)
    {
        return $this->repository->import($request);
    }
}
