<?php

namespace App\DataTables;

use App\Enums\ServiceTypeEnum;
use App\Helpers\Helpers;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use App\Enums\SymbolPositionEnum;

class ProviderSiteServiceDataTable extends DataTable
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
            ->editColumn('Image', function ($row) {
                $media = $row->getFirstMedia('image');
                if ($media) {
                    return '<img src="'.$media->getUrl().'" alt="Image" class="img-thumbnail img-fix">';
                }

                return '<img src="'.asset('admin/images/No-image-found.jpg').'" alt="Placeholder Image" class="img-thumbnail img-fix">';
            })
             ->editColumn('price', function ($row) use ($currencySymbol, $symbolPosition) {
            $formattedPrice = number_format($row->price, 2);

            return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . ' ' . $formattedPrice
                    : $formattedPrice . ' ' . $currencySymbol;
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.providerSiteService.status',
                    'value' => $row->status,
                ]);
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.providerSiteService.edit',
                    'edit_permission' => 'backend.service.edit',
                    'delete' => 'backend.providerSiteService.destroy',
                    'delete_permission' => 'backend.service.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('user.name', function ($row) {
                $user = $row?->user;
                return view('backend.inc.action', [
                    'info' => $user,
                    'route' => 'backend.provider.general-info'
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                if ($row->first() == 'Admin') {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input" id="disable-select" disabled></div>';
                }

                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'Image', 'provider name', 'user.name', 'price', 'created_at', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Service $model): QueryBuilder
    {
        if (auth()->user()->hasRole('provider')) {
            $services = $model->newQuery()->where('user_id', auth()->user()->id)->whereNull('parent_id')->where('service_type', ServiceTypeEnum::PROVIDER_SITE)->with('user');
        } else {
            $services = $model->newQuery()->whereNull('parent_id')->where('service_type', ServiceTypeEnum::PROVIDER_SITE)->with('user');
        }
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $services;
                }
            }
        }

        return $services->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');


        $builder->setTableId('service-table');

        if ($user->can('backend.service.destroy')) {
            $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
        }

        $builder->addColumn(['data' => 'Image', 'title' => __('static.image'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'title', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'user.name', 'title' => __('static.service.provider_name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'price', 'title' => __('static.service.price'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true]);

        if ($user->can('backend.service.edit') || $user->can('backend.service.destroy')) {
            if ($user->can('backend.service.edit')) {
                $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false]);
            }
            $builder->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);

        }

        return $builder->minifiedAjax()
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
        return 'Service_'.date('YmdHis');
    }
}
