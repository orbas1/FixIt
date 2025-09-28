<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use App\Enums\SymbolPositionEnum;

class ServiceRequestDataTable extends DataTable
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
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->editColumn('title', function ($row) use ($currencySymbol, $symbolPosition) {
                $media = $row->getFirstMedia('image');
                $imageUrl = $media ? $media->getUrl() : asset('admin/images/No-image-found.jpg');
                $imageTag = '<img src="'.$imageUrl.'" alt="Image" class="img-thumbnail img-fix">';

                 $formattedPrice = $row->initial_price ? number_format($row->initial_price, 2) : 'N/A';
                $price = $row->initial_price ? (
                    $symbolPosition === SymbolPositionEnum::LEFT->value ?
                    $currencySymbol . ' ' . $formattedPrice :
                    $formattedPrice . ' ' . $currencySymbol  
                ) : 'N/A';

                return '
                    <div class="service-list-item">
                        '.$imageTag.'
                        <div class="details">
                            <h5 class="mb-0">'.$row->title.'</h5>
                            <div class="info">
                                <span>Price: '.$price.'</span>
                            </div>
                        </div>
                    </div>
                ';

            })

            ->editColumn('provider_id', function ($row) {
                $provider = $row->provider;
                if ($provider) {
                    return view('backend.inc.action', [
                        'info' => $provider,
                        'ratings' => $row->provider->review_ratings,
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
                    'serviceRequest' => $row,
                    'delete' => 'backend.service-requests.destroy',
                    'delete_permission' => 'backend.service_request.destroy',
                    'data' => $row,
                ]);
            })
            ->rawColumns(['Image', 'checkbox' , 'title', 'provider_id', 'user.name', 'price', 'created_at']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ServiceRequest $model): QueryBuilder
    {
        if (auth()->user()->hasRole(RoleEnum::PROVIDER)) {
            $service_requests = $model->newQuery()->where('provider_id', auth()->user()->id)->with(['user','provider']);
        } else if(auth()->user()->hasRole(RoleEnum::CONSUMER)){
            $service_requests = $model->newQuery()->where('user_id', auth()->user()->id)->with(['provider','user']);
        } else {
            $service_requests = $model->newQuery()->with(['user', 'provider']);
        }
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (! isset(request()->columns[$index]['orderable'])) {
                    return $service_requests;
                }
            }
        }

        return $service_requests->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $builder->setTableId('service-requests-table');
        if (auth()->user()->hasRole(RoleEnum::PROVIDER)) {
            $service_requests = ServiceRequest::where('provider_id', auth()->user()->id)->get();
        } else if(auth()->user()->hasRole(RoleEnum::CONSUMER)){
            $service_requests = ServiceRequest::where('user_id', auth()->user()->id)->get();
        } else {
            $service_requests = ServiceRequest::get();
        }

        if ($user->can('backend.service_request.destroy')) {
            if($service_requests->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }

        $builder
            ->addColumn(['data' => 'title', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'provider_id', 'title' => __('static.service.provider_name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true]);
            if ($user->can('backend.service_request.index')) {
                $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false]);
            }
            if ($user->can('backend.service_request.destroy') || $user->can('backend.bid.index')) {
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
