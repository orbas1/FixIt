@extends('backend.layouts.master')

@section('title', __('static.language.languages'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('static.language.languages') }}</h5>
                    <div class="btn-action ms-auto">
                        @can('backend.language.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.systemLang.create') }}"
                                    class="btn">{{ __('static.language.create') }}
                                </a>
                            </div>
                        @endcan
                    </div>
                    <a href="#!" class="btn btn-sm btn-secondary deleteConfirmationBtn" style="display: none;"
                        data-url="{{ route('backend.delete.systemLang') }}">
                        <span id="count-selected-rows">0</span>{{ __('static.deleted_selected') }}</a>
                </div>
                <div class="card-body common-table">
                    <div class="language-table">
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
