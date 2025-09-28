@extends('backend.layouts.master')
@section('title', __('static.advertisement.create'))
@section('content')
    <form action="{{ route('backend.advertisement.store') }}" id="advertisementForm" method="POST"
        enctype="multipart/form-data">
        @csrf
        @include('backend.advertisement.fields')
   
    </form>
@endsection
