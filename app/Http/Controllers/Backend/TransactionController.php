<?php

namespace App\Http\Controllers\Backend;
use Exception;
use Illuminate\Http\Request;
use App\Models\PaymentTransactions;
use App\Http\Controllers\Controller;
use App\DataTables\TransactionsDataTable;
use App\Repositories\Backend\TaxRepository;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exceptions\ExceptionHandler;

class TransactionController extends Controller
{
    public $repository;

    public function __construct(TaxRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(TransactionsDataTable $dataTable)
    {
        return $dataTable->render('backend.transaction.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

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
    public function edit(PaymentTransactions $tax) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentTransactions $tax) {}

    public function status(Request $request, $id) {}

    public function deleteRows(Request $request) {}

    public function export(Request $request)
    {
        try {

            $format = $request->get('format', 'csv');
            switch ($format) {
                case 'excel':
                    return $this->exportExcel();
                case 'csv':
                default:
                    return $this->exportCsv();
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public  function exportExcel()
    {
        return Excel::download(new TransactionsExport, 'transactions.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new TransactionsExport, 'transactions.csv');
    }
}
