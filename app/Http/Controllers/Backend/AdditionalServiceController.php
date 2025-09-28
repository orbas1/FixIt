<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\AdditionalServiceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateAdditionalServiceRequest;
use App\Http\Requests\Backend\UpdateAdditionalServiceRequest;
use App\Models\Service;
use App\Repositories\Backend\AdditionalServiceRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class AdditionalServiceController extends Controller
{
    public $repository;

    public function __construct(AdditionalServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(AdditionalServiceDataTable $dataTable)
    {
        return $dataTable->render('backend.additional-service.index');
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
    public function store(CreateAdditionalServiceRequest $request)
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
    public function edit(Service $additionalService)
    {
        return $this->repository->edit($additionalService?->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateAdditionalServiceRequest $request,Service $additionalService)
    {
        return $this->repository->update($request, $additionalService?->id);
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
    public function destroy(Service $additionalService)
    {
        return $this->repository->destroy($additionalService?->id);
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
    public function export(Request $request)
    {
        return $this->repository->export($request);
    }
    public function import(Request $request)
    {
        return $this->repository->import($request);
    }

}
