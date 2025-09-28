<?php

namespace App\Http\Controllers\Backend;

use App\Models\Tax;
use Illuminate\Http\Request;
use App\DataTables\TaxDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\TaxRepository;
use App\Http\Requests\Backend\CreateTaxRequest;
use App\Http\Requests\Backend\UpdateTaxRequest;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(TaxRepository $repository)
    {
        $this->authorizeResource(Tax::class, 'tax');
        $this->repository = $repository;
    }

    public function index(TaxDataTable $dataTable)
    {
        return $dataTable->render('backend.tax.index');
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
    public function store(CreateTaxRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tax $tax)
    {
        return $this->repository->edit($tax?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxRequest $request, Tax $tax)
    {
        return $this->repository->update($request, $tax?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        return $this->repository->destroy($tax?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $tax = Tax::find($request->id[$row]);
                $tax->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
