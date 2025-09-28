@extends('backend.layouts.master')
@section('title', __('static.users.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.users.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form id="userForm" action="{{ route('backend.user.update', $user->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @includeIf('backend.user.fields')
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
