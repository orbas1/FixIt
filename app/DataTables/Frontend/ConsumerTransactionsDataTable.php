<?php

namespace App\DataTables\Frontend;

use Carbon\Carbon;
use App\Helpers\Helpers;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

use function Laravel\Prompts\alert;

class ConsumerTransactionsDataTable extends DataTable
{
    public $from;
    public $to;

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $currencySetting = Helpers::getSettings()['general']['default_currency'];
        $currencySymbol = $currencySetting->symbol;
        $symbolPosition = $currencySetting->symbol_position->value;

        return (new EloquentDataTable($query))
            ->editColumn('amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->amount, 2);
                if ($symbolPosition == 'left') {
                    return $currencySymbol . ' ' . $formattedAmount;
                } else {
                    return $formattedAmount . ' ' . $currencySymbol;
                }
            })
            ->editColumn('type', function ($row) {
                $labelClass = $row->type === 'credit' ? 'success' : 'danger';

                return '<span class="' . $labelClass . '-light">' . ucfirst($row->type) . '</span>';
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

    public function query(Transaction $model, Request $request): QueryBuilder
    {
        $userId = auth()->user()->id;
        $walletId = Helpers::getWalletIdByUserId($userId);

        $query = $model->newQuery()->where('wallet_id', $walletId);

        // Filter by date range if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay()
            ]);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('wallet-data')
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
        return 'Wallet_' . date('YmdHis');
    }
}
