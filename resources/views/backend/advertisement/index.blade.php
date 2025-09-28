@extends('backend.layouts.master')
@section('title', __('static.advertisement.advertisements'))

@section('content')
<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-align-center">
                <h5>{{ __('static.advertisement.advertisements') }}</h5>
                <div class="btn-action">
                    @can('backend.advertisement.create')
                        <div class="btn-popup mb-0">
                            <a href="{{ route('backend.advertisement.create') }}" class="btn">{{ __('static.advertisement.create') }}
                            </a>
                        </div>
                    @endcan
                    @can('backend.advertisement.destroy')
                    <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                    style="display: none;" data-url="{{ route('backend.delete.advertisements') }}">
                    <span id="count-selected-rows">0</span>{{__('static.delete_selected')}}
                    @endcan
                </a>
                </div>
            </div>
            <div class="card-body common-table">
                <div class="advertisement-table">
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
