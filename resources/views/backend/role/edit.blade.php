@extends('backend.layouts.master')
@section('title', __('static.roles.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <form id="role-form" action="{{ route('backend.role.update', $role->id) }}" method="POST"
                enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @include('backend.role.fields')
            </form>
        </div>
    </div>
@endsection
