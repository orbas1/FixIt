@extends('backend.layouts.master')

@section('title', __('static.tag.tags'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.tag.edit') }}</h5>
                    <div class="btn-action">

                    @can('backend.tag.destroy')
                        <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                            style="display: none;" data-url="{{ route('backend.delete.tags') }}">
                            <span id="count-selected-rows">0</span> {{ __('static.delete_selected') }}
                        </a>
                    @endcan
                </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.tag.update', $tag->id) }}" id="tagForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                    @include('backend.tag.fields')
                        <div class="text-end">
                            <button id='submitBtn' type="submit"
                                class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.tag.tags') }}</h5>
                </div>
                <div class="card-body common-table">
                    <div class="tag-table">
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
