@use('App\Models\Zone')
@php
    $zones = Zone::where('status', true)->get();
@endphp
@extends('backend.layouts.master')

@section('title', __('static.system_tools.activity_logs'))

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.system_tools.activity_logs') }}</h5>
                    <div class="btn-action">
                        <form action="{{ route('backend.activity-log.deleteAll') }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-delete-bin-5-line"></i> {{ __('static.delete_all') }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body common-table">
                    <div class="service-table">
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
