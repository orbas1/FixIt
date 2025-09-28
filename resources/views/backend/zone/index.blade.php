@extends('backend.layouts.master')

@section('title', __('static.zone.all'))

@section('content')


    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.zone.all') }}</h5>
                    <div class="btn-action">
                        @can('backend.zone.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.zone.create') }}" class="btn">{{ __('static.zone.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.zone.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.zones') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.deleted_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="zone-table">
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
@endpush
