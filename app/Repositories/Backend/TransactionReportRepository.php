<?php

namespace App\Repositories\Backend;

use Exception;
use App\Helpers\Helpers;
use App\Models\PaymentTransactions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exceptions\ExceptionHandler;
use App\Exports\TransactionsExport;
use Prettus\Repository\Eloquent\BaseRepository;

class TransactionReportRepository extends BaseRepository
{
    /**
     * Display a listing of the resource.
     */

    function model()
    {
        return PaymentTransactions::class;
    }

    public function index()
    {
        return view('backend.reports.transaction');

    }

    public function filter($request)
    {
        $transactions = $this->model;

        if($request->payment_method && !in_array('all', $request->payment_method)) {
            $transactions = $transactions->whereIn('payment_method',$request->payment_method);
        }

        if($request->payment_status && !in_array('all', $request->payment_status)) {
            $transactions = $transactions->whereIn('payment_status',$request->payment_status);
        }

        if($request->transaction_type && !in_array('all', $request->transaction_type)) {
            $transactions = $transactions->whereIn('type',$request->transaction_type);
        }

        if($request->start_end_date)
        {
            [$start_date, $end_date] = explode(' to ', $request->start_end_date);
            $transactions =  $transactions->whereBetween('created_at', [$start_date, $end_date]);
        }

        $transactions = $transactions->paginate(5);
        $transactionReportTable = $this->getTransactionReportTable($transactions, $request);

        return response()->json([
            'transactionReportTable' => $transactionReportTable,
            'pagination' => $transactions->links('pagination::bootstrap-5')->render(),
        ]);
    }

    public function getTransactionReportTable($transactions, $request)
    {
        $transactionReportTable = "";
        if($transactions->isNotEmpty())
        {
            foreach ($transactions as $transaction) {

                $transactionReportTable .= "
                    <tr>
                         <td>{$transaction->transaction_id}</td>
                        <td>{$transaction->payment_method}</td>
                        <td>
                        <label class='badge badge-" . $transaction->payment_status . "'>
                                " . ucfirst($transaction->payment_status) . "</label>
                                </td>

                                <td>" . Helpers::getDefaultCurrency()?->symbol . $transaction->amount . "</td>
                                <td>{$transaction->type}</td>

                    </tr>";
            }
        }
        else {
            $transactionReportTable .= "
            <tr>
                <td colspan='6' class='text-center'>
                    <div class='no-data'>
                        <span>" . __('static.no_records_found') . "</span>
                    </div>
                </td>
            </tr>";
        }


        return $transactionReportTable;
    }

    public function export($request)
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
