@extends('backend.layouts.master')
@section('title', __('static.provider.create'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.provider.create') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.provider.store') }}" id="providerForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @include('backend.provider.fields')
                    </form>
                    @include('backend.provider.address')
                </div>
            </div>
        </div>
    </div>
@endsection
