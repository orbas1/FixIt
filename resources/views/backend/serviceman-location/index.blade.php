@use('app\Helpers\Helpers')
@php
    $settings = Helpers::getSettings();
    if ($settings) {
        $googleMapKey = $settings['firebase']['google_map_api_key'] ?? null;
    }
@endphp

@extends('backend.layouts.master')
@section('title', __('static.serviceman.serviceman_list'))
@section('content')
    <div class="serviceman-location">
        <div class="position-relative">
            <button class="btn toggle-menu d-xl-none">
               <i data-feather="align-left"></i>
            </button>
            <div class="contentbox service-list-box">
                <div class="inside">
                    <div class="contentbox-title">
                        <h3>{{ __('static.serviceman.serviceman_list') }}</h3>
                        <button class="location-close-btn btn d-xl-none">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="search-box-group">
                        <input type="search" placeholder="Search here...." name="" class="form-control" id="">
                        <i class="ri-search-line"></i>
                    </div>
                    <ul class="location-list">
                        @foreach ($servicemen as $serviceman)
                            <li class="location-item" data-serviceman-id="{{ $serviceman['id'] }}"
                                data-lat="{{ $serviceman['lat'] ?? '' }}" data-lng="{{ $serviceman['lng'] ?? '' }}">
                                <div class="user-image">
                                    <img src="{{ $serviceman['image'] ?? asset('admin/images/user.png') }}" alt="serviceman"
                                        class="img-fluid">
                                </div>
                                <div class="user-name">
                                    <div>
                                        <h5 class="name">{{ $serviceman['name'] }}</h5>
                                        <div class="rate-box">
                                            <i class="ri-star-fill"></i>
                                            {{ $serviceman['review'] ? number_format($serviceman['review'], 1) : 'Unrated' }}
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary view-location-btn"
                                        data-serviceman-id="{{ $serviceman['id'] }}"
                                        data-lat="{{ $serviceman['lat'] ?? '' }}" data-lng="{{ $serviceman['lng'] ?? '' }}">
                                        <span class="btn-text d-md-block d-none">{{ __('static.serviceman.view_location') }}</span>
                                        <i data-feather="navigation" class="d-md-none"></i>
                                        <span class="spinner-border spinner-border-sm d-none"></span>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @if ($googleMapKey)
                <div class="location-map">
                    <div id="map_canvas"></div>
                    <iframe allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d184552.6740963969!2d-79.54286422140463!3d43.71812280590592!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89d4cb90d7c63ba5%3A0x323555502ab4c477!2sToronto%2C%20ON%2C%20Canada!5e0!3m2!1sen!2sin!4v1743684885848!5m2!1sen!2sin"></iframe>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($googleMapKey)
    @include('backend.serviceman-location.google')
@endif

@push('js')
    <script>
        $(".toggle-menu").click(function() {
            $(".service-list-box").addClass("show");
        });

        $(".location-close-btn").click(function () {
            $(".service-list-box").removeClass("show");
        });
    </script>
@endpush