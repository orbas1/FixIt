@extends('backend.layouts.master')
@section('title', __('static.document.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.document.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.document.update', $document->id) }}" id="documentForm"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.document.fields')
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
