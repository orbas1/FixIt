<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\CommissionHistory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use App\Enums\SymbolPositionEnum;

class CommissionDataTable extends DataTable
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
            ->setRowId('id')
            ->editColumn('booking.booking_number', function ($row) {
                if (!$row->booking) {
                    return '<span>N/A</span>';
                }
                return view('backend.inc.action', [
                    'collaps' => [
                        'booking_data'=> $row->booking,
                        'primary_on_click_url' => route('backend.booking.showChild', $row->booking),
                    ]
                ])->render();
            })
            ->editColumn('provider.name', function ($row) {
                $provider = $row->provider;
                return view('backend.inc.action', [
                    'info' => $provider,
                    'ratings' => $provider?->review_ratings,
                    'route' => 'backend.provider.general-info'
                ]);
            })
            ->editColumn('admin_commission', function ($row) use ($currencySymbol, $symbolPosition) {
            $formattedAmount = number_format($row->admin_commission, 2);

                $formattedPrice = ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount
                    : $formattedAmount . ' ' . $currencySymbol;

                return $formattedPrice;
            })
            ->editColumn('provider_commission', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->provider_commission, 2);

                $formattedPrice = ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount
                    : $formattedAmount . ' ' . $currencySymbol;

                return $formattedPrice;
            })
            ->rawColumns(['admin_commission', 'provider_commission','checkbox', 'booking.booking_number',]);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CommissionHistory $model): QueryBuilder
    {
        $roleName = Helpers::getCurrentRoleName();
        $query = $model->newQuery()
            ->select('commission_histories.*')
            ->leftJoin('users as provider', 'commission_histories.provider_id', '=', 'provider.id')
            ->with('provider', 'booking');
    
        if ($roleName == RoleEnum::PROVIDER) {
            $query->where('commission_histories.provider_id', auth()->user()?->id);
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
            ->setTableId('commission-table')
            ->addColumn(['data' => 'booking.booking_number', 'title' => __('static.commission_histories.booking_no'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'provider.name', 'title' => __('static.commission_histories.provider_name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'admin_commission', 'title' => __('static.commission_histories.admin_commission'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'provider_commission', 'title' => __('static.commission_histories.provider_commission'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'booking.platform_fees', 'title' => __('static.commission_histories.platform_fees'), 'orderable' => true, 'searchable' => true])
            ->orderBy(1)
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
        return 'Commission_'.date('YmdHis');
    }
}
