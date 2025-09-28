<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Booking;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserBookingsDataTable extends DataTable
{

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('booking_number', function ($row) {
                return view('backend.inc.action', [
                    'collaps' => [
                        'booking_data'=> $row,
                        'primary_on_click_url' => route('backend.booking.show', $row->id),
                    ]
                ]);
            })
            ->editColumn('service.name', function ($row) {
                if (count($row->sub_bookings)) {
                    return '<a href="'.route('backend.booking.show',$row->id).'" class="form-controll">'.$row->sub_bookings->first()?->service?->title.'</a>';
                } else if (isset($row->service?->title)) {
                    return '<a href="'.route('backend.booking.show',$row->id).'" class="form-controll">'.$row->service?->title.'</a>';
                }

                return '<a href="#" class="form-controll">N/A</a>';
            })
            ->editColumn('booking_status', function ($row) {
                if (count($row->sub_bookings)) {
                    return '<span class="badge booking-status-'.$row->sub_bookings?->first()?->booking_status->color_code.'">'.$row->sub_bookings?->first()?->booking_status->name.'</span>';
                } else if (isset($row->booking_status?->color_code)) {
                    return '<span class="badge booking-status-'.$row->booking_status->color_code.'">'.$row->booking_status->color_code.'</span>';
                }

                return '<span class="form-controll">N/A</span>';
            })
            ->editColumn('provider_name', function ($row) {
                if (count($row->sub_bookings)) {
                    $provider = $row->sub_bookings->first()?->provider;
                } else {
                    $provider = $row->provider;
                }
                return view('backend.inc.action', [
                    'info' => $provider,
                ]);
            })
            ->editColumn('consumer_id', function ($row) {
                if (count($row->sub_bookings)) {
                    $consumer = $row->sub_bookings->first()?->consumer;
                } else {
                    $consumer = $row->consumer;
                }
                return view('backend.inc.action', [
                    'info' => $consumer,
                ]);
            })
            ->editColumn('payment_status', function ($row) {
                if (count($row->sub_bookings)) {
                    return '<lable class="badge '.$row->sub_bookings->first()?->payment_status.'">'.$row->sub_bookings->first()?->payment_status.'</lable>';
                } else if (isset($row->payment_status)) {
                    return '<lable class="badge '.$row->payment_status.'">'.$row->payment_status.'</lable>';
                }

                return '<lable class="form-controll">N/A</lable>';
            })
            ->editColumn('payment_method', function ($row) {
                return ucfirst($row->payment_method);
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'show' => 'backend.booking.show',
                    'data' => $row,
                ]);
            })->rawColumns(['action', 'consumer_id', 'created_at', 'booking_number', 'service.name', 'payment_method', 'payment_status', 'booking_status', 'provider_name']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Booking $model): QueryBuilder
    {
        $roleName = Helpers::getRoleByUserId(request()->id);
        $bookings = $model->newQuery()->whereNull('parent_id')->without(['consumer']);
        if ($roleName == RoleEnum::PROVIDER) {
            $bookings = $model->newQuery()->whereNull('parent_id')->whereHas('sub_bookings', function ($query) {
                $query->where('provider_id', request()->id);
            });    
        } else if ($roleName == RoleEnum::SERVICEMAN) {
            $bookingParentIds = $model->newQuery()?->whereHas('servicemen', function(Builder $servicemen) {
                $servicemen->where('users.id', request()->id);
            })->pluck('parent_id')->toArray();

            $bookings = $model->newQuery()->whereIn('id', $bookingParentIds);
        } else if ($roleName == RoleEnum::CONSUMER) {
            $bookings = $model->newQuery()->whereNull('parent_id')->whereHas('sub_bookings', function ($query) {
                $query->where('consumer_id', request()->id);
            });
        }

        if (request()->status) {
            $booking_status_id = Helpers::getbookingStatusIdBySlug(request()->status);
            $bookings = $bookings->whereHas('sub_bookings', function($sub_bookings) use ($booking_status_id) {
                return $sub_bookings->where('booking_status_id', $booking_status_id);
            });
        }
        
        return $bookings->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');
        
        return $this->builder()
            ->setTableId('user-booking-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()->parameters([
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
        return [
            Column::make('booking_number')->title(__('static.booking.booking_number'))->orderable(true)->searchable(true),
            Column::make('service.name')->title(__('static.booking.service_name'))->orderable(false)->searchable(false),
            Column::make('consumer_id')->title(__('static.booking.consumer_name'))->searchable(true),
            Column::make('provider_name')->title(__('static.booking.provider_name'))->orderable(false)->searchable(false),
            Column::make('payment_method')->title(__('static.booking.payment_method'))->orderable(true)->searchable(true),
            Column::make('booking_status')->title(__('static.booking.booking_status_id'))->orderable(false)->searchable(false),
            Column::make('payment_status')->title(__('static.booking.payment_status'))->orderable(false)->searchable(false),
            Column::make('created_at')->title(__('static.booking.created_at'))->orderable(true)->searchable(false),
            Column::make('action')->title(__('static.booking.action'))->orderable(false)->searchable(false),
        ];
    }
}
