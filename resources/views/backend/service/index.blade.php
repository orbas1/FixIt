@use('App\Models\Zone')
@php
    $zones = Zone::where('status', true)->get();
@endphp
@extends('backend.layouts.master')

@section('title', __('static.service.services'))

@section('content')

    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.service.services') }}</h5>

                    <div class="btn-action">
                        @can('backend.service.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.service.create') }}"
                                    class="btn">{{ __('static.service.create') }}</a>
                            </div>
                        @endcan
                        @can('backend.service.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.services') }}">
                                <span id="count-selected-rows">0</span> {{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="service-table">
                        <div class="booking-select common-table-select">
                            <select class="select-2 form-control" id="zoneFilter"
                                data-placeholder="{{ __('static.notification.select_zone') }}">
                                <option class="select-placeholder" value=""></option>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone->id }}" @if (request()->zone == $zone->id) selected @endif>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {!! $dataTable->scripts() !!}
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('#zoneFilter').change(function() {
                    var selectedStatus = $(this).val();
                    var newUrl = "{{ route('backend.service.index') }}";
                    if (selectedStatus) {
                        newUrl += '?zone=' + selectedStatus;
                    }
                    // table.ajax.url(newUrl).load();
                    location.href = newUrl;
                });
            });
        })(jQuery);
    </script>
@endpush
