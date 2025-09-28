<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ServiceRequestDataTable;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Repositories\Backend\ServiceRequestRepository;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public $repository;

    public function __construct(ServiceRequestRepository $repository)
    {
        $this->authorizeResource(ServiceRequest::class, 'service_request');
        $this->repository = $repository;
    }

    public function index(ServiceRequestDataTable $dataTable)
    {
        return $dataTable->render('backend.service-request.index');
    }

    public function create()
    {  
        return $this->repository->create();
    }

    public function edit(ServiceRequest $serviceRequest)
    {  
        return $this->repository->edit($serviceRequest->id);
    }

    public function store(Request $request)
    {
        return $this->repository->store($request);
    }

    public function update(Request $request,ServiceRequest $serviceRequest)
    {
        return $this->repository->update($request, $serviceRequest?->id);
    }
    
    public function destroy(ServiceRequest $serviceRequest)
    {
        return $this->repository->destroy($serviceRequest?->id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $service = ServiceRequest::find($request->id[$row]);
                $service->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
