@extends('backend.layouts.master')
@section('title', __('static.coupon.coupons'))
@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('static.coupon.coupons') }}</h5>
                    <div class="btn-action">
                        @can('backend.coupon.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.coupon.create') }}" class="btn">{{ __('static.coupon.create') }}
                                </a>
                            </div>
                        @endcan
                        <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                            style="display: none;" data-url="{{ route('backend.delete.coupons') }}">
                            <span id="count-selected-rows">0</span>Delete Selected
                        </a>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="coupon-table">
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
