@extends('backend.layouts.master')
@section('title', __('static.provider-document.create'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.provider-document.create') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.provider-document.store') }}" id="providerDocumentForm"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('backend.provider-document.fields')
                        <div class="card-footer">
                            <button class="btn btn-primary spinner-btn"
                                type="submit">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
