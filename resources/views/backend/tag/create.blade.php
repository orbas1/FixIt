@extends('backend.layouts.master')

@section('title', __('static.tag.create'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.tag.create') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.tag.store') }}" id="tagForm" method="POST">
                        @csrf
                        @include('backend.tag.fields')
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
