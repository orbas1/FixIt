@extends('backend.layouts.master')

@section('title', __('static.page.create'))

@section('content')
<form action="{{ route('backend.page.store') }}" id="pageForm" method="POST" enctype="multipart/form-data">
    @csrf
    @include('backend.page.fields')
</form>
@endsection
