@extends('backend.layouts.master')

@section('title', __('static.language.edit'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.language.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form id="languageForm" action="{{ route('backend.systemLang.update', @$language?->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        @includeIf('backend.language.fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
