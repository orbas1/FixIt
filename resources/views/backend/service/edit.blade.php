@extends('backend.layouts.master')
@section('title', __('static.service.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.service.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.service.update', $service->id) }}" id="serviceForm"
                        method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        @include('backend.service.fields')
                    </form>
                    @include('backend.service.address')
                </div>
            </div>
        </div>
    </div>
@endsection
