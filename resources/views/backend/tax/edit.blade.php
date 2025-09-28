@extends('backend.layouts.master')

@section('title', __('static.tax.edit'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.tax.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.tax.update', $tax->id) }}" id="taxForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @includeIf('backend.tax.fields')
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
