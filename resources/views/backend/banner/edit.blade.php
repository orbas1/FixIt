@extends('backend.layouts.master')
@section('title', __('static.banner.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.banner.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.banner.update', $banner->id) }}" id="bannerForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.banner.fields')
                        <div class="text-end">
                            <button id='submitBtn' type="submit"
                                class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
