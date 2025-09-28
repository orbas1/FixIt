<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProviderTransactionsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Repositories\Backend\ProviderWalletRepository;
use Illuminate\Http\Request;

class ProviderWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ProviderWalletRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(ProviderTransactionsDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function creditOrdebit(Request $request)
    {
        return $this->repository->creditOrdebit($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
    public function edit(Wallet $tax)
    {
        return $this->repository->edit($tax);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $tax)
    {
        return $this->repository->destroy($tax->id);
    }

    public function updateStatus(Request $request)
    {
        return $this->repository->updateStatus($request);
    }

    public function providerWalletTransations(Request $request, $provider_id)
    {
        return $this->repository->getProvidertransations($request, $provider_id);
    }
}
