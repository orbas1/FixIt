@extends('backend.layouts.master')
@section('title', __('static.provider_site_service.edit'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.provider_site_service.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.providerSiteService.update', $providerSiteService->id) }}"
                        id="serviceForm" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        @include('backend.providerSiteService.fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
