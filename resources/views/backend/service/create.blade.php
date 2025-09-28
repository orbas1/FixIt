@extends('backend.layouts.master')
@section('title', __('static.service.create'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.service.create') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.service.store') }}" id="serviceForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @include('backend.service.fields')
                    </form>
                    @include('backend.service.address')
                </div>
            </div>
        </div>
    </div>
@endsection
