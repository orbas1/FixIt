@extends('backend.layouts.master')

@section('title', __('static.zone.create'))

@section('content')
<div class="row">
    <div class="m-auto col-12-8">
        <div class="card tab2-card">
            <div class="card-header">
                <h5>{{ __('static.zone.create') }}</h5>
            </div>
            <div class="card-body map-box">
                <div class="row g-lg-5 g-md-4 g-3">
                    <div class="col-xl-7 order-xl-1 order-last">
                        <form action="{{ route('backend.zone.store') }}" id="zoneForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('backend.zone.fields')
                            <div class="text-end">
                                <button id='submitBtn' type="button" class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-xl-5 order-xl-2 order-1">
                        <div class="map-instruction">
                            <h4>{{ __('static.zone.map_instruction_heading') }}</h4>
                            <p>{{ __('static.zone.map_instruction_title') }}</p>
                            <div class="map-detail">
                                <i data-feather="move"></i>
                                {{ __('static.zone.map_instruction_paragraph_1') }}
                            </div>
                            <div class="map-detail">
                                <i data-feather="pen-tool"></i>
                                {{ __('static.zone.map_instruction_paragraph_2') }}
                            </div>
                            <img src="{{ asset('admin/images/map.gif') }}" class="notify-img">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
