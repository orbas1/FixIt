<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\ServicemanTransactionsDataTable;
use App\Repositories\Backend\ServicemanWalletRepository;

class ServicemanWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ServicemanWalletRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(ServicemanTransactionsDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function creditOrDebit(Request $request)
    {
        return $this->repository->creditOrDebit($request);
    }

    public function servicemanWalletTransactions(Request $request, $provider_id)
    {
        return $this->repository->servicemanWalletTransactions($request, $provider_id);
    }
}
