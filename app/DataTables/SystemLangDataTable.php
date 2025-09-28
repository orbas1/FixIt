<?php

namespace App\DataTables;

use App\Helpers\Helpers;
use App\Models\SystemLang;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class SystemLangDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', 'systemLang.action')
            ->setRowId('id')
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('is_rtl', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'is_rtl',
                    'route' => 'backend.systemLang.rtl',
                    'value' => $row->is_rtl,
                ]);
            })

            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.systemLang.status',
                    'value' => $row->status,
                ]);
            })

            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.systemLang.edit',
                    'edit_permission' => 'backend.language.edit',
                    'delete' => 'backend.systemLang.destroy',
                    'delete_permission' => 'backend.language.destroy',
                    'translate' => 'backend.systemLang.translate',
                    'data' => $row,
                ]);
            })

            ->addColumn('checkbox', function ($row) {
                $isDefaultLang = Helpers::isDefaultLang($row->id);
                $disabled = $isDefaultLang ? 'disabled' : '';
                return '<div class="form-check">
                            <input type="checkbox" name="row" class="rowClass form-check-input" value=' . $row->id . ' id="rowId' . $row->id . '" ' . $disabled . '>
                        </div>';
            })
            ->rawColumns(['checkbox', 'is_rtl', 'action', 'role', 'created_at', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SystemLang $model): QueryBuilder
    {
        $systemLangs = $model->newQuery();
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $systemLangs;
                }
            }
        }

        return $systemLangs->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $languages = SystemLang::get();
        $builder->setTableId('language-table');
        if ($user->can('backend.language.destroy')) {
            if($languages->count() > 1) {
            $builder ->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }

        $builder ->addColumn(['data' => 'name', 'title' => __('static.language.name'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'locale', 'title' => __('static.language.locale'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'app_locale', 'title' => __('static.language.app_locale'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false]);
        if ($user->can('backend.language.destroy') || $user->can('backend.language.edit')) {
            if ($user->can('backend.language.edit')) {
                $builder->addColumn(['data' => 'is_rtl', 'title' => __('static.language.is_rtl'), 'orderable' => true, 'searchable' => false])
                ->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false]);
            }

            $builder ->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => true, 'searchable' => false]);
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
        return 'SystemLang_'.date('YmdHis');
    }
}
