@extends('backend.layouts.master')

@section('title', __('static.provider-document.provider-documents'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.provider-document.provider-documents') }}</h5>
                    <div class="btn-action">
                        @can('backend.provider_document.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.provider-document.create') }}"
                                    class="btn">{{ __('static.provider-document.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.provider_document.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.providerDocuments') }}">
                                <span id="count-selected-rows">0</span>{{ __('static.delete_selected') }}
                            @endcan
                        </a>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="provider-document-table">
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
