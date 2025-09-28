<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use App\Helpers\Helpers;

class ReviewsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->editColumn('description', function ($row) {
                return $row->description ?? $row->service?->reviews?->first()?->description;
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'data' => $row,
                    'delete' => 'backend.review.destroy',
                    'review' => 'backend.review.edit',
                ]);
            })
            ->editColumn('consumer.name', function ($row) {
                return view('backend.inc.action', [
                    'info' => $row->consumer,
                ]);
            })

            ->setRowId('id')
            ->rawColumns(['checkbox']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Review $model): QueryBuilder
    {
        $roleName = Helpers::getRoleByUserId(request()->id);

        $reviews = $model->newQuery()->whereNotNull('service_id')->with('service');
            if ($roleName == RoleEnum::PROVIDER) {
                $reviews = $model->newQuery()->whereNotNull('service_id')->where('reviews.provider_id', request()?->id)->with('service');
            } else if ($roleName == RoleEnum::SERVICEMAN) {
                $reviews = $model->newQuery()->whereNotNull('service_id')->where('reviews.serviceman_id', request()?->id)->with('service');
            } else if ($roleName == RoleEnum::CONSUMER) {
                $reviews = $model->newQuery()->whereNotNull('service_id')->where('reviews.consumer_id', request()?->id)->with('service');
            }

            if (request()->order) {
                if ((bool) head(request()->order)['column']) {
                    $index = head(request()->order)['column'];
                    if (!isset(request()->columns[$index]['orderable'])) {
                        return $reviews;
                    }
                }
            }

            return $reviews->orderBy('reviews.created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $reviews = Review::whereNotNull('service_id')->get();
        $builder->setTableId('review-table');
        if ($user->can('backend.review.destroy')) {
            if($reviews->count() > 1) {
            $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows"/> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }

        $builder->addColumn(['data' => 'consumer.name', 'title' => __('static.serviceman.customer'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'service.title', 'title' => __('static.serviceman.service'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'rating', 'title' => __('static.serviceman.rating'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'description', 'title' => __('static.serviceman.description'), 'orderable' => true, 'searchable' => true]);

        if ($user->can('backend.review.destroy')) {
            $builder->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);

        }

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
        return 'UserReview_'.date('YmdHis');
    }
}
