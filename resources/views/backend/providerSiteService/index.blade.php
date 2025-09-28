@extends('backend.layouts.master')

@section('title', __('static.provider_site_service.provider_site_services'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.provider_site_service.provider_site_services') }}</h5>
                    <div class="btn-action">
                        @can('backend.service.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.providerSiteService.create') }}"
                                    class="btn">{{ __('static.provider_site_service.create') }}</a>
                            </div>
                        @endcan
                        @can('backend.service.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.providerSiteServices') }}">
                                <span id="count-selected-rows">0</span> {{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="service-table">
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
