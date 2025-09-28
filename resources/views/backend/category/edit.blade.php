@extends('backend.layouts.master')
@section('title', Request::route('backend.category.edit', $cat->id) ? __('static.categories.edit') :
    __('static.categories.categories'))
    @push('style')
        <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/tree.css') }}">
    @endpush
@section('content')
    <div class="row g-sm-4 g-2">
        <div class="col-xxl-4 col-xl-5 col-12">
            @include('backend.category.tree', [
                'categories' => $categories->all(),
                'cat' => $cat,
            ])
        </div>
        <div class="col-xxl-8 col-xl-7 col-12">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.categories.edit_category') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.category.update', $cat->id) }}" id="categoryForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.category.fields')
                        <div class="text-end">
                            <button id='submitBtn' type="submit"
                                class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
