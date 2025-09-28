@extends('backend.layouts.master')
@section('title', __('static.blog.edit'))
@section('content')
    <form action="{{ route('backend.blog.update', $blog->id) }}" id="blogForm" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        @include('backend.blog.fields')
 
    </form>
@endsection
