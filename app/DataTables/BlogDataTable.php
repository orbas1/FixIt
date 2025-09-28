<?php

namespace App\DataTables;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Session;

class BlogDataTable extends DataTable
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
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('blog.categories', function ($row) {
                $categories = $row->categories->take(2)->pluck('title')->toArray();

                return view('backend.inc.action',
                    ['categories' => $categories]
                );
            })
            ->editColumn('title', function ($row) {
                $locale = Session::get('locale', app()->getLocale());
                $titleLink = '<a href="'.route('backend.blog.edit', ['blog' => $row->id, 'locale' => $locale]).'" class="text-decoration-none">'.$row->title.'</a>';
                $media = $row->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                })->first();
                $imageUrl = $media ? $media->getUrl() : asset('admin/images/No-image-found.jpg');
                $imageTag = '<img src="'.$imageUrl.'" alt="Image" class="img-thumbnail img-fix">';
                return '
                    <div class="service-list-item">
                        '.$imageTag.'
                        <div class="details">
                            <h5 class="mb-0">'.$titleLink.'</h5>
                            <div class="info"></div>
                        </div>
                    </div>
                ';
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.blog.edit',
                    'locale' => Session::get('locale', app()->getLocale()),
                    'delete' => 'backend.blog.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.blog-status',
                    'value' => $row->status,
                ]);
            })
            ->editColumn('is_featured', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'is_featured',
                    'route' => 'backend.isFeatured',
                    'value' => $row->is_featured,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value=' . $row->id . ' id="rowId' . $row->id . '"></div>';
            })
            ->rawColumns(['checkbox', 'action', 'created_at', 'status', 'title']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Blog $model): QueryBuilder
    {
        $blogs = $model->newQuery();
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (!isset(request()->columns[$index]['orderable'])) {
                    return $blogs;
                }
            }
        }

        return $blogs->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $blogs = Blog::get();
        $builder->setTableId('blog-table');
        if ($user->can('backend.blog.destroy')) {
            if($blogs->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }

        $builder->addColumn(['data' => 'title', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'blog.categories', 'title' => __('static.service.category'), 'orderable' => false, 'searchable' => false]);

        if ($user->can('backend.blog.destroy') || $user->can('backend.blog.destroy')) {
            if ($user->can('backend.blog.destroy')) {
                $builder->addColumn(['data' => 'is_featured', 'title' => __('static.blog.featured'), 'orderable' => true, 'searchable' => false])
                    ->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false]);
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
        return 'Blog_' . date('YmdHis');
    }
}
