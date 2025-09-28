<?php

namespace App\Exports;

use App\Models\PaymentTransactions;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionsExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $transactions = PaymentTransactions::latest('created_at');
        return $this->filter($transactions, request()->all());
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
           'item_id',
           'payment_method',
           'payment_status',
           'type',
           'amount',
           'transaction_id',                                            
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->item_id,
            $transaction->payment_method,
            $transaction->payment_status,
            $transaction->type,
            $transaction->amount,
            $transaction->transaction_id,
        ];
    }

     /**
     * Get the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Item Id',
            'Payment Method',
            'Payment Status',
            'Type',
            'Amount',
            'Transaction Id', 
        ];
    }

    public function filter($transactions, $request)
    {
        if (isset($request['payment_method']) && !empty($request['payment_method'])) {
            $transactions = $transactions->whereIn('payment_method', $request['payment_method']);
        }

        if (isset($request['payment_status']) && !empty($request['payment_status'])) {
            $transactions = $transactions->whereIn('payment_status', $request['payment_status']);
        }

        if (isset($request['transaction_type']) && !empty($request['transaction_type'])) {
            $transactions = $transactions->whereIn('type', $request['transaction_type']);
        }

        if (isset($request['start_end_date']) && !empty($request['start_end_date'])) {
            [$start_date, $end_date] = explode(' to ', $request['start_end_date']);
            $transactions = $transactions->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $transactions->get();
    }
}
