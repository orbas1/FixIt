@extends('firebase::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('firebase.name') !!}</p>
@endsection
