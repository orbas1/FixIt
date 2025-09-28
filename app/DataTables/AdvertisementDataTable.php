<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Session;
use App\Enums\SymbolPositionEnum;

class AdvertisementDataTable extends DataTable
{

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $currencySetting = Helpers::getSettings()['general']['default_currency'];
        $currencySymbol = $currencySetting->symbol;
        $symbolPosition = $currencySetting->symbol_position; 

        return (new EloquentDataTable($query))
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value=' . $row->id . ' id="rowId' . $row->id . '"></div>';
            })
            ->editColumn('provider.name', function ($row) {
                $provider = $row->provider;
                if ($provider) {
                    return view('backend.inc.action', [
                        'info' => $provider,
                        'ratings' => $provider->review_ratings,
                        'route' => 'backend.provider.general-info'
                    ]);
                }
                return 'N/A';
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('price', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedPrice = number_format($row->price, 2);

                $price = $row->price ? (
                    $symbolPosition === SymbolPositionEnum::LEFT ? 
                    $currencySymbol . '' . $formattedPrice :
                    $formattedPrice . ' ' . $currencySymbol
                ) : 'N/A';

                return $price;
            })
            ->editColumn('zone', function ($row)  {

                return $row?->zone_id?->name ?? 'N/A' ;
            })
            ->editColumn('start_date', function ($row) {
                return date('d-M-Y', strtotime($row->start_date)). ' to ' .date('d-M-Y', strtotime($row->end_date));
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.advertisement.edit',
                    'status' => 'backend.advertisement.status',
                    'status_permission' => 'backend.advertisement.edit',
                    'locale' => Session::get('locale', app()->getLocale()),
                    'delete' => 'backend.advertisement.destroy',
                    'data' => $row,
                ]);
            })
            ->rawColumns(['checkbox', 'Image', 'created_at',  'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Advertisement $model): QueryBuilder
    {
        $advertisements = $model->newQuery();
        if(Helpers::getCurrentRoleName() == RoleEnum::PROVIDER){
            $advertisements?->where('provider_id' , Helpers::getCurrentUserId());
        }
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (!isset(request()->columns[$index]['orderable'])) {
                    return $advertisements->latest();
                }
            }
        }

        return $advertisements->latest();
    }


    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $advertisements = Advertisement::get();
        $builder->setTableId('advertisement-table');
        if ($user->can('backend.advertisement.destroy')) {
            if($advertisements->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }

        $builder->addColumn(['data' => 'provider.name', 'title' => __('static.provider_time_slot.provider_name'), 'orderable' => false, 'searchable' => true])
            ->addColumn(['data' => 'screen', 'screen' => __('static.advertisement.screen'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'type', 'title' => __('static.advertisement.type'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'price', 'title' => __('static.advertisement.price'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'start_date', 'title' => __('static.advertisement.duration'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'zone', 'title' => __('static.advertisement.zone'), 'orderable' => true, 'searchable' => true])

            ->addColumn(['data' => 'status', 'title' => __('static.advertisement.status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'created_at', 'title' => __('static.advertisement.created_at'), 'orderable' => true, 'searchable' => true]);
            $builder->addColumn(['data' => 'action', 'title' => __('static.advertisement.action'), 'orderable' => false, 'searchable' => false]);

        return $builder->minifiedAjax()
            ->orderBy(4)
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
        return 'Banner_' . date('YmdHis');
    }
}
