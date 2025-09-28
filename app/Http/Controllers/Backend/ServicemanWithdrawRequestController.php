<?php

namespace App\Http\Controllers\Backend;

use Exception;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\WithdrawRequest;
use App\Http\Controllers\Controller;
use App\Exceptions\ExceptionHandler;
use App\Models\ServicemanWithdrawRequest;
use App\DataTables\ServicemanWithdrawRequestDataTable;
use App\Http\Requests\Backend\CreateServicemanWithdrawRequest;
use App\Http\Requests\Backend\UpdateServicemanWithdrawRequest;
use App\Repositories\Backend\ServicemanWithdrawRequestRepository;

class ServicemanWithdrawRequestController extends Controller
{
    public $repository;

    public function __construct(ServicemanWithdrawRequestRepository $repository)
    {
        $this->authorizeResource(ServicemanWithdrawRequest::class, 'serviceman_withdraw_request', [
            'except' => 'destroy',
        ]);

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ServicemanWithdrawRequestDataTable $dateTable)
    {
        try {
            return $dateTable->render('backend.serviceman-withdraw-request.index');

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
    public function store(CreateServicemanWithdrawRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        return $this->repository->show($servicemanWithdrawRequest->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicemanWithdrawRequest $request, ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        return $this->repository->update($request->all(), $servicemanWithdrawRequest->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServicemanWithdrawRequest $servicemanWithdrawRequest)
    {
        //
    }

    public function filter($servicemanWithdrawRequest, $request)
    {
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName == RoleEnum::SERVICEMAN) {
            $servicemanWithdrawRequest = $this->repository->where('serviceman_id', Helpers::getCurrentUserId());
        }

        if ($request->field && $request->sort) {
            $servicemanWithdrawRequest = $servicemanWithdrawRequest->orderBy($request->field, $request->sort);
        }

        if ($request->start_date && $request->end_date) {
            $servicemanWithdrawRequest = $servicemanWithdrawRequest->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return $servicemanWithdrawRequest;
    }
}
