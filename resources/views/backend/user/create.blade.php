@extends('backend.layouts.master')
@section('title', __('static.users.create'))
@section('content')
    @can('backend.user.create')
        <div class="row">
            <div class="m-auto col-xl-10 col-xxl-8">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>{{ __('static.users.create') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="userForm" action="{{ route('backend.user.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @include('backend.user.fields')
                            <div class="text-end">
                                <button id='submitBtn' type="submit"
                                    class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
