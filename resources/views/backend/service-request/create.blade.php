@extends('backend.layouts.master')
@section('title', __('static.service_request.create'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.service_request.create') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.service-requests.store') }}" id="serviceRequestForm"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('backend.service-request.fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
