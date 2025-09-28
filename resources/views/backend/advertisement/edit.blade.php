@extends('backend.layouts.master')
@section('title', __('static.advertisement.edit'))
@section('content')
    <form action="{{ route('backend.advertisement.update', $advertisement->id) }}" id="advertisementForm" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('backend.advertisement.fields')
    </form>
@endsection
