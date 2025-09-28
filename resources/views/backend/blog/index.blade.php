@extends('backend.layouts.master')
@section('title', __('static.blog.blogs'))
@section('content')
<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex-align-center">
                <h5>{{ __('static.blog.blogs') }}</h5>
                <div class="btn-action">
                    @can('backend.blog.create')
                        <div class="btn-popup mb-0">
                            <a href="{{ route('backend.blog.create') }}" class="btn">{{ __('static.blog.create') }}
                            </a>
                        </div>
                    @endcan
                    @can('backend.blog.destroy')
                    <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                        style="display: none;" data-url="{{ route('backend.delete.blogs') }}">
                        <span id="count-selected-rows">0</span> {{__('static.delete_selected')}}
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body common-table">
                <div class="blog-table">
                    <div class="table-responsive">
                        {!! $dataTable->table() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
    {!! $dataTable->scripts() !!}
@endpush
