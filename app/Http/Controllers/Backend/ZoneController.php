<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ZoneDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateZoneRequest;
use App\Http\Requests\Backend\UpdateZoneRequest;
use App\Models\Zone;
use App\Repositories\Backend\ZoneRepository;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ZoneRepository $repository)
    {
        $this->authorizeResource(Zone::class, 'zone');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ZoneDataTable $dataTable)
    {
        return $dataTable->render('backend.zone.index');
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
    public function store(CreateZoneRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        return $this->repository->edit($zone?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateZoneRequest $request,Zone $zone)
    {
        return $this->repository->update($request->all(), $zone?->id);
    }

    public function destroy(Zone $zone)
    {
        return $this->repository->destroy($zone?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $zone = Zone::find($request->id[$row]);
                $zone->delete();
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
