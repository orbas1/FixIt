@extends('backend.layouts.master')
@section('title', __('static.categories.categories'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/tree.css') }}">
@endpush
@section('content')
    <div class="row g-sm-4 g-2">
        @can('backend.blog_category.index')
            <div class="col-xxl-4 col-xl-5 col-12">
                @include('backend.blog-category.tree', [
                    'categories' => $categories->where('category_type', 'blog'),
                    'cat' => isset($cat) ? $cat : null,
                ])
            </div>
        @endcan
        @can('backend.blog_category.create')
            <div class="col-xxl-8 col-xl-7 col-12">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>{{ __('static.categories.create') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('backend.blog-category.store') }}" id="categoryForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('backend.blog-category.fields')
                            <div class="text-end">
                                <button id='submitBtn' type="submit" class="btn ms-auto btn-primary spinner-btn">{{ __('static.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/jstree.min.js') }}"></script>
    <script>
        (function($) {
            'use strict';
            var tree_custom = {
                init: function() {
                    $('#treeBasic').jstree({
                        'core': {
                            'themes': {
                                'responsive': false,
                            },
                        },
                        'types': {
                            'default': {
                                'icon': 'ti-gallery'
                            },
                            'file': {
                                'icon': 'ti-file'
                            }
                        },
                        "search": {
                            "case_insensitive": true,
                            "show_only_matches": true
                        },
                        'plugins': ['types', 'search']
                    });

                    $('#search').keyup(function() {
                        $('#treeBasic').jstree('search', $(this).val());
                    });
                }
            };
            $(document).ready(function() {
                tree_custom.init();

                setTimeout(function() {
                    $('.jstree-loader').fadeOut('slow');
                    $('#treeBasic').show();
                }, 500);
            });
        })(jQuery);
    </script>
@endpush
