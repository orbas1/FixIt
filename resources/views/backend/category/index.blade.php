@extends('backend.layouts.master')
@section('title', __('static.categories.categories'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/tree.css') }}">
@endpush
@section('content')
    <div class="row g-sm-4 g-2">
        @can('backend.service_category.index')
            <div class="col-xxl-4 col-xl-5 col-12">
                @include('backend.category.tree', [
                    'categories' => $categories->all(),
                    'cat' => isset($cat) ? $cat : null,
                ])
            </div>
        @endcan
        @can('backend.service_category.create')
            <div class="col-xxl-8 col-xl-7 col-12">
                <div class="row g-sm-4 g-3">
                    <div class="col-12">
                        <div class="card tab2-card p-sticky">
                            <div class="card-header">
                                <h5>{{ __('static.categories.create') }}</h5>
                            </div>
                            <form action="{{ route('backend.category.store') }}" id="categoryForm" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    @include('backend.category.fields')
                                    <div class="text-end">
                                        <button id='submitBtn' type="submit"
                                            class="btn btn-primary ms-auto spinner-btn">{{ __('static.submit') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- <div class="col-12">
                            <div class="card tab2-card p-sticky">
                                <div class="card-header">
                                    <h5>{{ __('static.categories.create') }}</h5>
                                </div> 
                            <form action="{{ route('backend.category.store') }}" id="categoryForm" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    @include('backend.category.fields')
                                    <div class="footer">
                                        <button id='submitBtn' type="submit"
                                            class="btn btn-primary spinner-btn">{{ __('static.submit') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> --}}
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/jstree.min.js') }}"></script>
@endpush
