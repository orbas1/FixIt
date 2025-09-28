<?php

namespace App\DataTables;

use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class ProviderDocumentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('Image', function ($row) {

                $media = $row->getFirstMedia('provider_documents');
                    if ($media) {
                        return '<img src="'.$media->getUrl().'" alt="Image" class="img-thumbnail img-fix">';
                    }
                    return '<img src="'.asset("admin/images/No-image-found.jpg").'" alt="Placeholder Image" class="img-thumbnail img-fix">';
                })
                ->editColumn('user.name', function ($row) {
                    $provider = $row->user;
                    if ($provider) {
                        return view('backend.inc.action', [
                            'info' => $provider,
                            'ratings' => $provider->review_ratings,
                            'route' => 'backend.provider.general-info'
                        ]);
                    }

                })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('status', function ($row) {
                $labelClass = '';
                switch ($row->status) {
                    case 'approved':
                        $labelClass = 'success';
                        break;
                    case 'pending':
                        $labelClass = 'warning';
                        break;
                    case 'rejected':
                        $labelClass = 'danger';
                        break;
                }

                return '<span class="badge badge-'.$labelClass.'-light">'.ucfirst($row->status).'</span>';
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.provider-document.edit',
                    'edit_permission' => 'backend.provider_document.edit',
                    'delete' => 'backend.provider-document.destroy',
                    'delete_permission' => 'backend.provider_document.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                if ($row->first() == 'Admin') {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input" id="disable-select" disabled></div>';
                }

                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'Image', 'action', 'created_at', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserDocument $model): QueryBuilder
    {

        $provider_documents = $model->newQuery();
        if (request()->id) {
            $provider_documents->where('user_id', request()->id);
        }

        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (!isset(request()->columns[$index]['orderable'])) {
                    return $provider_documents;
                }
            }
        }

        return $provider_documents->orderBy('created_at', 'desc');

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $documents = UserDocument::get();
        $builder->setTableId('providerdocument-table');
        if ($user?->can('backend.provider_document.destroy')) {
            if($documents->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }
        $builder->addColumn(['data' => 'Image', 'title' => __('static.image'), 'orderable' => false, 'searchable' => false])
        ->addColumn(['data' => 'user.name', 'title' => __('static.provider_time_slot.provider_name'), 'orderable' => false, 'searchable' => true])
        ->addColumn(['data' => 'identity_no', 'title' => __('static.document_no'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true]);

        if ($user->can('backend.provider_document.edit') || $user->can('backend.provider_document.destroy')) {
            if ($user->can('backend.provider_document.edit')) {
                $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => true]);
            }

            $builder->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);

        }

        return $builder->minifiedAjax()
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
        return 'ProviderDocument_'.date('YmdHis');
    }
}
