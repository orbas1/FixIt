@extends('backend.layouts.master')
@section('title', __('static.coupon.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.coupon.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.coupon.update', $coupon->id) }}" id="couponForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('coupon::fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
