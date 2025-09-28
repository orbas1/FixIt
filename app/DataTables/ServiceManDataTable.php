<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class ServiceManDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->editColumn('email', function ($row) {

                return view('backend.inc.action', [
                    'info' => $row,
                    'ratings' => $row->ServicemanReviewRatings,
                    'route' => 'backend.servicemen.general-info'
                ]);
            })
            ->editColumn('provider.name', function ($row) {
                $provider = $row->provider;
                if ($provider) {
                    return view('backend.inc.action', [
                        'info' => $provider,
                        'ratings' => $row->review_ratings,
                        'route' => 'backend.provider.general-info'
                    ]);
                }
                return 'N/A';
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.serviceman.edit',
                    'delete' => 'backend.serviceman.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.serviceman.status',
                    'value' => $row->status,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'merged', 'created_at', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        $roleName = auth('web')->user()->roles->pluck('name')->first();
        $query = $model->newQuery()->with('provider'); 

        if ($roleName == RoleEnum::ADMIN) {
            $query->role('serviceman')->where('system_reserve', 0);
        } else if ($roleName == RoleEnum::PROVIDER){
            $query->where('provider_id', auth()->user()->id)->where('system_reserve', 0)->role(RoleEnum::SERVICEMAN);
        } else {
            $query->where('created_by', auth()->user()->id)->where('system_reserve', 0)->role(RoleEnum::SERVICEMAN);
        }

        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $query;
                }
            }
        }
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('serviceman-table')
            ->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'email', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'provider.name', 'title' => __('static.provider.provider'), 'orderable' => false, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false])
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
        return 'ServiceMan_'.date('YmdHis');
    }
}
