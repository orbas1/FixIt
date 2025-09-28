<?php

namespace App\DataTables;

use App\Helpers\Helpers;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use App\Enums\SymbolPositionEnum;

class ConsumerTransactionsDataTable extends DataTable
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

        return (new EloquentDataTable($query))
            ->editColumn('amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->amount, 2);
                return $symbolPosition === SymbolPositionEnum::LEFT 
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
            })
            ->editColumn('type', function ($row) {
                $labelClass = $row->type === 'credit' ? 'success' : 'danger';

                return '<span class="badge badge-'.$labelClass.'-light">'.ucfirst($row->type).'</span>';
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->setRowId('id')
            ->rawColumns(['type', 'amount', 'created_at']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Transaction $model): QueryBuilder
    {
        $walletId = Helpers::getWalletIdByUserId(request()->user_id);

        return $model->newQuery()->where('wallet_id', $walletId);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('consumertransactions-table')
            ->addColumn(['data' => 'amount', 'title' => __('static.transactions.amount'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'type', 'title' => __('static.transactions.type'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'detail', 'title' => __('static.transactions.detail'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.transactions.created_at'), 'orderable' => true, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(3)
            ->selectStyleSingle()
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
        return 'ConsumerTransactions_'.date('YmdHis');
    }
}
