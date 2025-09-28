@extends('backend.layouts.master')

@section('title', __('static.serviceman.edit'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.serviceman.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.serviceman.update', $serviceman->id) }}" id="servicemanForm"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.serviceman.fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
