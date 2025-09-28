<?php

namespace App\DataTables;

use App\Helpers\Helpers;
use App\Models\PaymentTransactions;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use App\Enums\SymbolPositionEnum;

class TransactionsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $currencySetting = Helpers::getSettings()['general']['default_currency'];
        $currencySymbol = $currencySetting->symbol;
        $symbolPosition = $currencySetting->symbol_position;

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->amount, 2);
                
                return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
            })
            ->editColumn('payment_status', function ($row) {
                if (isset($row->payment_status)) {
                    return '<lable class="badge badge-'.$row->payment_status.'">'.$row->payment_status.'</lable>';
                }

                return '<lable class="form-controll">N/A</lable>';
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->rawColumns(['created_at','payment_status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PaymentTransactions $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('transactions-table')
            ->addColumn(['data' => 'item_id', 'title' => __('static.transaction.item_id'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'amount', 'title' => __('static.transaction.amount'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'transaction_id', 'title' => __('static.transaction.transaction_id'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'payment_method', 'title' => __('static.transaction.payment_method'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'payment_status', 'title' => __('static.transaction.payment_status'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'type', 'title' => __('static.transaction.type'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false])
            ->minifiedAjax()
            ->parameters([
                'language' => [
                    'emptyTable' => $no_records_found,
                    'infoEmpty' => '',
                    'zeroRecords' => $no_records_found,
                ],
                'drawCallback' => 'function(settings) {
                    if (settings._iRecordsDisplay === 0) {
                        $(settings.nTableWrapper).find(".dataTables_paginate").hide();
                    } else {
                        $(settings.nTableWrapper).find(".dataTables_paginate").show();
                    }
                    feather.replace();
                }',
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Transactions_'.date('YmdHis');
    }
}