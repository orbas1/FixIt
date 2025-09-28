<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class ProviderDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function ($row) {

                return view('backend.inc.action', [
                    'info' => $row,
                    'ratings' => $row->review_ratings,
                    'route' => 'backend.provider.general-info'
                ]);
            })
            ->editColumn('users.bookings', function ($row) {
                return $row->bookings->count();
            })
            ->editColumn('users.servicemans', function ($row) {

                return $row->servicemans->count();
            })
            ->editColumn('type', function ($row) {
                return ucfirst($row->type);
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('action', function ($row) {
                if ($row->getRoleNames()->first() == 'Admin') {
                    return '<p class="text-success">System Reserved</p>';
                }

                return view('backend.inc.action', [
                    'edit' => 'backend.provider.edit',
                    'delete' => 'backend.provider.destroy',
                    'providerWallet' => 'backend.provider-wallet.creditOrdebit',
                    'providerDocument' => 'backend.provider-document.index',
                    'data' => $row,
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.provider.status',
                    'value' => $row->status,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                if ($row->getRoleNames()->first() == 'Admin' || $row->system_reserve) {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input" id="disable-select" disabled></div>';
                }

                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'name', 'action', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        $providers = $model->newQuery()->role(RoleEnum::PROVIDER)->where('system_reserve', 0);
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $providers;
                }
            }
        }

        return $providers->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $provider = User::role(RoleEnum::PROVIDER)->where('system_reserve', 0)->get();
        $builder->setTableId('provider-table');
        if ($user?->can('backend.provider.destroy')) {
            if($provider->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }
        $builder->addColumn(['data' => 'name', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
                ->addColumn(['data' => 'type', 'title' => __('static.type'), 'orderable' => true, 'searchable' => true])
                ->addColumn(['data' => 'users.bookings', 'title' => __('static.total_bookings'), 'orderable' => false, 'searchable' => false])
                ->addColumn(['data' => 'users.servicemans', 'title' => __('static.total_servicemen'), 'orderable' => false, 'searchable' => false])
                ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true]);

        if ($user->can('backend.provider.edit') || $user->can('backend.provider.destroy')) {
            if ($user->can('backend.provider.edit')) {
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
        return 'Provider_'.date('YmdHis');
    }
}
