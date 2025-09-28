@extends('backend.layouts.master')

@section('title', __('static.testimonials.testimonials'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.testimonials.testimonials') }}</h5>
                    <div class="btn-action">
                        @can('backend.testimonial.create')
                            <div class="btn-popup mb-0">
                                <a href="{{ route('backend.testimonial.create') }}"
                                    class="btn">{{ __('static.testimonials.create') }}
                                </a>
                            </div>
                        @endcan
                        @can('backend.testimonial.destroy')
                            <a href="javascript:void(0);" class="btn btn-sm btn-secondary deleteConfirmationBtn"
                                style="display: none;" data-url="{{ route('backend.delete.testimonials') }}">
                                <span id="count-selected-rows">0</span> {{ __('static.delete_selected') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="page-table">
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
