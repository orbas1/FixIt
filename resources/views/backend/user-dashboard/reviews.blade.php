@extends('backend.layouts.master')

@section('title', __('static.user_dashboard.reviews'))

@section('content')

    <div class="card-body bg-white user-details-dashboard">
        <div class="row">
            <div class="m-auto col-12-8">
                <div class="card tab2-card">
                    @includeIf('backend.user-dashboard.index')
                    <div class="card">
                        <div class="card-body common-table pt-0">
                            <div class="reviews-table">
                                <div class="table-responsive">
                                    {!! $dataTable->table() !!}
                                </div>
                            </div>
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
