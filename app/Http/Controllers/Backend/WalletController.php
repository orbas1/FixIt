<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ConsumerTransactionsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Repositories\Backend\WalletRepository;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(WalletRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(ConsumerTransactionsDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ConsumerTransactionsDataTable $dataTable)
    {
        return $this->repository->create($dataTable);
    }

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

    public function walletTransations(Request $request, $user_id)
    {
        return $this->repository->getUsertransations($request, $user_id);
    }
}
