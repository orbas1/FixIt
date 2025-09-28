@extends('backend.layouts.master')

@section('title', __('static.roles.roles'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.roles.roles') }}</h5>
                    <div class="btn-action">
                        @can('backend.role.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.role.create') }}" class="btn">{{ __('static.roles.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.role.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.roles') }}">
                                <span id="count-selected-rows">0</span> {{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="role-table">
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
