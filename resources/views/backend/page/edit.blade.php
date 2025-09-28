@extends('backend.layouts.master')

@section('title', __('static.page.edit'))

@section('content')
    <form action="{{ route('backend.page.update', $page->id) }}" id="pageForm" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('backend.page.fields')
    </form>
@endsection
