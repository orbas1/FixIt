@use('app\Helpers\Helpers')
@php
    $settings = Helpers::getSettings();
    if($settings){
        $googleMapKey = $settings['firebase']['google_map_api_key'] ?? null;
    }
@endphp

@extends('backend.layouts.master')
@section('title', __('static.serviceman.serviceman_list'))
@section('content')
    <div>
        <div class="row g-md-4 g-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <!-- Serviceman List -->
                <div class="contentbox service-list-box">
                    <div class="inside">
                        <div class="contentbox-title">
                            <h3>{{ __('static.serviceman.serviceman_list') }}</h3>
                        </div>
                        <ul class="location-list">
                            @foreach ($servicemen as $serviceman)
                                <li class="location-item" data-serviceman-id="{{ $serviceman['id'] }}" data-lat="{{ $serviceman['lat'] ?? '' }}"
                                    data-lng="{{ $serviceman['lng'] ?? '' }}">
                                    <div class="user-image">
                                        <img src="{{ $serviceman['image'] ?? asset('admin/images/user.png') }}" alt="serviceman" class="img-fluid">
                                    </div>
                                    <div class="user-name">
                                        <div>
                                            <h5 class="name">{{ $serviceman['name'] }}</h5>
                                            <div class="rate-box">
                                                <i class="ri-star-fill"></i>
                                                {{ $serviceman['review'] ? number_format($serviceman['review'], 1) : 'Unrated' }}
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary view-location-btn" data-serviceman-id="{{ $serviceman['id'] }}" data-lat="{{ $serviceman['lat'] ?? '' }}" data-lng="{{ $serviceman['lng'] ?? '' }}">
                                            <span class="btn-text">{{ __('static.serviceman.view_location') }}</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @if ($googleMapKey)
            <div class="col-xl-8 col-lg-7 col-md-6">
                <div class="p-sticky">
                    <div class="contentbox service-list-box">
                        <div class="inside">
                            <div class="contentbox-title d-block">
                                <div class="contentbox-subtitle justify-content-between">
                                    <h3>{{ __('static.serviceman.serviceman_location') }}</h3>
                                    <button id="show-all-servicemen" class="btn clear-btn" style="display: none;">{{ __('static.serviceman.show_all') }}</button>
                                </div>
                            </div>
                            <div id="map_canvas" style="width: 100%; height: 500px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@if ($googleMapKey)
    @include('backend.serviceman-location.google')
@endif
