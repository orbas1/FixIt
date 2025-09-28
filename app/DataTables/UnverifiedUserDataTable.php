<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use App\Enums\RoleEnum;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class UnverifiedUserDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('role', function ($row) {
                return ucfirst($row->getRoleNames()->first());

            })
            ->editColumn('email', function ($row) {
                return view('backend.inc.action', [
                    'info' => $row,

                ]);
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('is_verified', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'is_verified',
                    'route' => 'backend.unverfied-users.action',
                    'value' => $row->is_verified,
                ]);
            })
            ->editColumn('action', function ($row) {
                if ($row->getRoleNames()->first() == 'Admin') {
                    return '<p class="text-success">System Reserved</p>';
                }

                return view('backend.inc.action', [
                    'delete' => 'backend.user.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                if ($row->getRoleNames()->first() == 'Admin' || $row->system_reserve) {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input" id="disable-select" disabled></div>';
                }

                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId' . $row->id . '"></div>';
            })
            ->rawColumns(['checkbox', 'role', 'created_at', 'is_verified']);
    }

    /**
     * Get the query source of dataTable.
     */
    /**
 * Get the query source of dataTable.
 */
public function query(User $model): QueryBuilder
{
    $unverifiedUser = $model->with('roles')
        ->where('is_verified', false)->where('system_reserve',false);

    if (request()->has('role') && !empty(request()->role)) {
        $role = request()->role;
        $unverifiedUser = $unverifiedUser->whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        });
    }

    $unverifiedUser = $unverifiedUser->whereDoesntHave('roles', function ($query) {
        $query->where('name', RoleEnum::ADMIN);
    });

    if (request()->order) {
        if ((bool) head(request()->order)['column']) {
            $index = head(request()->order)['column'];
            if (!isset(request()->columns[$index]['orderable'])) {
                return $unverifiedUser;
            }
        }
    }
    return $unverifiedUser->orderBy('created_at', 'desc');
}


    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $unverifiedUser = User::where('is_verified', false)->where('system_reserve',false);

        $unverifiedUser = $unverifiedUser->whereDoesntHave('roles', function ($query) {
            $query->where('name', RoleEnum::ADMIN);
        });

        if ($user?->can('backend.user.destroy')) {
            if($unverifiedUser->count() > 1) {
            $builder->setTableId('user-table')
            ->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
        }
    }
        $builder->addColumn(['data' => 'email', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'role', 'title' => __('static.roles.role'), 'orderable' => false, 'searchable' => false])
        ->addColumn(['data' => 'is_verified', 'title' => __('static.is_verified'), 'orderable' => false, 'searchable' => false])
        ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false])
        ->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);

        return $builder->minifiedAjax()
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
        return 'User_' . date('YmdHis');
    }
}
