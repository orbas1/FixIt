

@extends('backend.layouts.master')
@section('title', __('static.blog.create'))
@section('content')
<form action="{{ route('backend.blog.store') }}" id="blogForm" method="POST" enctype="multipart/form-data">
        @csrf
        @include('backend.blog.fields')
    </form>
@endsection
