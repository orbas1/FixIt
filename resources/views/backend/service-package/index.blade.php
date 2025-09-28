@extends('backend.layouts.master')

@section('title', __('static.service_package.service_packages'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.service_package.service_packages') }}</h5>
                    <div class="btn-action">
                        @can('backend.service-package.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.service-package.create') }}"
                                    class="btn">{{ __('static.service_package.create') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="service-package-table">
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
