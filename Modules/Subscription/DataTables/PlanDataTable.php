<?php

namespace Modules\Subscription\DataTables;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Subscription\Entities\Plan;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class PlanDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $currencySymbol = Helpers::getSettings()['general']['default_currency']->symbol;
        return (new EloquentDataTable($query))
            ->editColumn('action', 'plan.action')
            ->setRowId('id')
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('price', function ($row) use ($currencySymbol) {
                return $currencySymbol.''.$row->price;
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.plan.edit',
                    'delete' => 'backend.plan.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.plan.status',
                    'value' => $row->status,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'action', 'role', 'created_at', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Plan $model): QueryBuilder
    {
        $plans = $model->newQuery();
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $plans;
                }
            }
        }

        return $plans->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');


        $builder->setTableId('tag-table');
        if ($user->can('backend.plan.destroy')) {
            $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
        }

        $builder->addColumn(['data' => 'name', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'price', 'title' => __('static.price'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'duration', 'title' => __('static.plan.duration'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false]);

        if ($user->can('backend.plan.destroy') || $user->can('backend.plan.edit') ) {
            if ($user->can('backend.plan.edit')) {
                $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => false, 'searchable' => false]);
            }

            $builder->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false])
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

        return $builder;
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
        return 'Plan_'.date('YmdHis');
    }
}
