@extends('backend.layouts.master')
@section('title', __('static.roles.create'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <form id="role-form" action="{{ route('backend.role.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('backend.role.fields')
            </form>
        </div>
    </div>
@endsection
