@extends('backend.layouts.master')
@section('title', Request::route('backend.category.edit',$cat->id ) ? __('static.categories.edit') :__('static.category.categories'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/tree.css') }}">
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-4">
            @include('backend.blog-category.tree', [
                'categories' => $categories,
                'cat' => $cat,
            ])
        </div>
        <div class="col-sm-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.categories.edit_category') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.blog-category.update', $cat->id) }}" id="categoryForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.blog-category.fields')
                        <div class="text-end">
                            <button id='submitBtn' type="submit" class="btn ms-auto btn-primary spinner-btn">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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

                $('#treeBasic').on('click', '.edit-icon', function(e) {
                    var id = $(this).attr('value');
                    window.location.href = '/backend/blog-category/' + id + '/edit';
                });

                $('#treeBasic').on('click', '.edit-child', function(e) {
                    var id = $(this).attr('value');
                    window.location.href = '/backend/blog-category/' + id + '/edit';
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

