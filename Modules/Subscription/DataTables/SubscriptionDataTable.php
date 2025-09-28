<?php

namespace Modules\Subscription\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Subscription\Entities\UserSubscription;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class SubscriptionDataTable extends DataTable
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
            ->editColumn('user.name', function ($row) {
                $user = $row->user;
                if ($user) {
                    return view('backend.inc.action', [
                        'info' => $user,
                        'route' => 'backend.provider.general-info'
                    ]);
                }
                return ''; 
            })
            ->editColumn('plan.name', function ($row) {
                return $row->plan->name ?? 'N/A';
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('start_date', function ($row) {
                return date('d-m-Y', strtotime($row->start_date));
            })
            ->editColumn('end_date', function ($row) {
                return date('d-m-Y', strtotime($row->start_date));
            })
            ->editColumn('is_active', function ($row) {
                switch ($row->is_active) {
                    case '1':
                        $labelClass = 'success';
                        $text = 'active';
                        break;
                    case '0':
                        $labelClass = 'danger';
                        $text = 'expired';
                        break;
                }

                return '<span class="badge badge-'.$labelClass.'-light">'.ucfirst($text).'</span>';
            })
            ->rawColumns(['created_at', 'is_active', 'start_date', 'end_date']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserSubscription $model): QueryBuilder
    {
        $subscriptions = $model->newQuery()->with(['plan', 'user']);
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $subscriptions;
                }
            }
        }

        return $subscriptions->orderBy('user_subscriptions.created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('subscription-table')
             ->addColumn(['data' => 'user.name', 'title' =>  __('static.plan.provider'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'plan.name', 'title' => __('static.plan.plan'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'total', 'title' =>  __('static.plan.total'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'start_date', 'title' => __('static.plan.plan_start_at'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'end_date', 'title' =>  __('static.plan.plan_expire_at'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'is_active', 'title' => __('static.plan.active'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(6)
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
        return 'Plan_'.date('YmdHis');
    }
}
