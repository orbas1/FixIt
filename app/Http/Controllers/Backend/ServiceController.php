<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ServiceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateServiceRequest;
use App\Http\Requests\Backend\UpdateServiceRequest;
use App\Models\Service;
use App\Repositories\Backend\ServiceRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->authorizeResource(Service::class, 'service');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ServiceDataTable $dataTable)
    {
        return $dataTable->render('backend.service.index');
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
    public function store(CreateServiceRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit(Service $service)
    {
        return $this->repository->edit($service?->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateServiceRequest $request,Service $service)
    {
        return $this->repository->update($request, $service?->id);
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
    public function destroy(Service $service)
    {
        return $this->repository->destroy($service?->id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $service = Service::find($request->id[$row]);
                $service->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getZoneCategories(Request $request)
    {
        return $this->repository->getZoneCategories($request);
    }

    public function getZoneTaxes(Request $request)
    {
        return $this->repository->getZoneTaxes($request);
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
