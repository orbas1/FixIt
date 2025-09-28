<?php

namespace App\DataTables;

use App\Helpers\Helpers;
use App\Models\ServicemanWithdrawRequest;
use App\Models\WithdrawRequest;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use App\Enums\SymbolPositionEnum;

class ServicemanWithdrawRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
         $currencySetting = Helpers::getSettings()['general']['default_currency'];
        $currencySymbol = $currencySetting?->symbol;
        $symbolPosition = $currencySetting?->symbol_position;
        $currencySymbol = Helpers::getSettings()['general']['default_currency']?->symbol;

        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->editColumn('user.name', function ($row) {
                $user = $row->user;
                if ($user) {
                    return view('backend.inc.action', [
                        'info' => $user,
                        'ratings' => $row->user->review_ratings,
                        'route' => 'backend.provider.general-info'
                    ]);
                }
                return ''; 
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'withdraw_request' => 'backend.serviceman-withdraw-request.update',
                    'permission' => 'backend.serviceman_withdraw_request.action',
                    'data' => $row,
                ]);
            })
            ->editColumn('amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->amount, 2);

                if ($symbolPosition === SymbolPositionEnum::LEFT) {
                    return $currencySymbol . '' . $formattedAmount; 
                } else {
                    return $formattedAmount . ' ' . $currencySymbol; 
                }
            })
            ->editColumn('status', function ($row) {
                switch ($row->status_alias) {
                    case 'approved':
                        $labelClass = 'success';
                        break;
                    case 'pending':
                        $labelClass = 'warning';
                        break;
                    case 'rejected':
                        $labelClass = 'danger';
                        break;
                    default:
                        $labelClass = 'warning';
                        break;
                }

                return '<span class="badge badge-'.$labelClass.'-light">'.ucfirst($row->status).'</span>';
            })

            ->rawColumns(['checkbox', 'action', 'created_at', 'payment_type', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ServicemanWithdrawRequest $model): QueryBuilder
    {
        $withdrawRequests = $model->newQuery()->with('user')->select('serviceman_withdraw_requests.*', 'serviceman_withdraw_requests.status as status_alias');
        
        if (auth()->user()->hasRole('serviceman')) {
            $withdrawRequests = $model->newQuery()->with('user')->select('serviceman_withdraw_requests.*', 'serviceman_withdraw_requests.status as status_alias')->where('serviceman_withdraw_requests.serviceman_id', auth()->user()->id);
        } else if (request()->id) {
            $withdrawRequests = $model->newQuery()->with('user')->select('serviceman_withdraw_requests.*', 'serviceman_withdraw_requests.status as status_alias')->where('serviceman_withdraw_requests.serviceman_id', request()->id);
        }

        if (request()->draw > 1) {
            if (isset(head(request()->order)['column'])) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $withdrawRequests;
                }
            }
        }

        return $withdrawRequests->orderBy('serviceman_withdraw_requests.created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('servicemanWithdrawRequest-table')
            ->addColumn(['data' => 'user.name', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'amount', 'title' => __('static.withdraw.amount'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'status', 'title' => __('static.withdraw.status'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()

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
        return 'ServicemanWithdrawRequest_'.date('YmdHis');
    }
}
