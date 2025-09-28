<?php

namespace App\Http\Controllers\Backend;

use App\Models\TimeSlot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\ProviderTimeSlotDataTable;
use App\Http\Requests\Backend\CreateTimeSlotRequest;
use App\Http\Requests\Backend\UpdateTimeSlotRequest;
use App\Repositories\Backend\ProviderTimeSlotRepository;

class ProviderTimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ProviderTimeSlotRepository $repository)
    {
        $this->authorizeResource(TimeSlot::class, 'provider_time_slot');
        $this->repository = $repository;
    }

    public function index(ProviderTimeSlotDataTable $dataTable)
    {
        return $dataTable->render('backend.provider-time-slot.index');
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
    public function store(CreateTimeSlotRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeSlot $provider_time_slot)
    {
        return $this->repository->edit($provider_time_slot->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeSlotRequest $request, TimeSlot $provider_time_slot)
    {
        return $this->repository->update($request, $provider_time_slot?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeSlot $provider_time_slot)
    {
        return $this->repository->destroy($provider_time_slot?->id);
    }

    public function updateStatus(Request $request)
    {
        return $this->repository->updateStatus($request->statusVal, $request->subject_id);
    }

    public function deleteRows(Request $request)
    {
        return $this->repository->deleteRows($request);
    }

}
