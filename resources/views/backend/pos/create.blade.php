@extends('backend.layouts.master')

@section('title', __('static.booking.create'))

{{-- Swiper Slider css --}}
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@use('app\Helpers\Helpers')
@use('app\Models\State')
@php
    $countries = Helpers::getCountries();
    $countryCodes = Helpers::getCountryCodes();
    $states = [];
    if (isset($address->country_id) || old('country_id')) {
        $states = State::where('country_id', old('country_id', @$address->country_id))?->get();
    }
@endphp
<link rel="stylesheet" href="{{ asset('admin/css/vendors/swiper-slider.css') }}">
@section('content')
    @use('App\Enums\ServiceTypeEnum')
    @use('App\Enums\FrontEnum')
    <div class="pos-section">
        <div class="row g-sm-4 g-3">
            <div class="col-xl-8">
                <div class="left-box sticky-box">
                    <div class="row g-sm-4 g-3">
                        <div class="col-12">
                            <div class="contentbox bg-transparent">
                                <div class="inside p-0">
                                    <div class="contentbox-title">
                                        <div class="contentbox-subtitle">
                                            <h3>Category List</h3>
                                            <div class="slider-buttons">
                                                <div class="swiper-button-prev category-button-prev">
                                                    <i class="ri-arrow-left-s-line"></i>
                                                </div>
                                                <div class="swiper-button-next category-button-next">
                                                    <i class="ri-arrow-right-s-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper category-slider pos-category-box nav nav-tabs">
                                        <div class="swiper-wrapper">

                                                    @foreach($categories as $index => $category)
                                                    <div class="swiper-slide nav-item">
                                                            <div class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                                id="tab-{{ $index }}" data-bs-toggle="tab"
                                                                data-bs-target="#category-{{ $index }}" type="button" role="tab">
                                                                @php
                                                                    $locale = app()->getLocale();
                                                                    $mediaItems = $category->getMedia('image')->filter(function ($media) use ($locale) {
                                                                        return $media->getCustomProperty('language') === $locale;
                                                                    });
                                                                    $imageUrl = $mediaItems->count() > 0 ? $mediaItems->first()->getUrl() : FrontEnum::getPlaceholderImageUrl();
                                                                @endphp
                                                                <div class="category-box">
                                                                    <div class="category-image">
                                                                        <img src="{{ Helpers::isFileExistsFromURL($imageUrl, true) }}"
                                                                            class="img-fluid" alt="{{ $category?->title }}">
                                                                    </div>
                                                                    <h5>{{ $category?->title }}</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="serviceListContainer">
                            @include('backend.pos.service-list', ['categories' => $categories])
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="details-servicemen-box">
                    <div class="card">
                        <div class="card-body">
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="contentbox bg-transparent">
                                        <div class="inside p-0">
                                            <div class="contentbox-title">
                                                <div class="contentbox-subtitle">
                                                    <h3>Find A Service</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12 select-dropdown">
                                            <select class="select-2 form-control user-dropdown" name="provider_id" id="providerDropdown"
                                                data-placeholder="{{ __('static.pos.find_service_by_provider') }}" required>
                                                <option class="select-placeholder" value=""></option>
                                                @foreach ($providers as $provider)
                                                    <option value="{{ $provider->id }}" data-provider-type="{{ $provider->type }}" sub-title="{{ $provider->email }}" image="{{ $provider->getFirstMedia('image')?->getUrl() }}" {{ $provider->id == request()->query('provider_id') ? 'selected' : '' }}>
                                                        {{ $provider->name }} ({{ $provider->type }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('provider_id')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">

                                        <div class="col-12">
                                        <input class="form-control" type="text" id="service-search" name="name" value="" placeholder="Find Service">
                                            @error('phone')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="checkoutForm" method="post" >
                        <div class="card">
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="form-group row">
                                            <div class="col-12 d-flex flex-column-reverse select-dropdown">
                                                <select class="select-2 form-control user-dropdown" name="consumer_id" id="consumerDropdown"
                                                    data-placeholder="{{ __('static.pos.find_service_by_consumer') }}">
                                                    <option class="select-placeholder" value=""></option>
                                                    @foreach ($consumers as $consumer)
                                                        <option value="{{ $consumer->id }}" sub-title="{{ $consumer->email }}"
                                                            image="{{ $consumer->getFirstMedia('image')?->getUrl() }}"
                                                            {{ $consumer->id == request()->query('consumer_id') ? 'selected' : '' }}>
                                                            {{ $consumer->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('consumer_id')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="contentbox bg-transparent">
                                            <div class="inside p-0">
                                                <div class="contentbox-title">
                                                    <div class="contentbox-subtitle">
                                                        <h3>Added Items details</h3>
                                                        <button class="btn clear-btn confirmationBtn" type="button" id="clear-cart-button">Clear all</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="cart-list-box">
                                            @includeIf('backend.pos.cart-list' , ['cartItems' => $cartItems])
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="discount-box">
                                            <div class="form-group row">
                                                <div class="col-12">
                                                    <div class="radio-buttons-box mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="discount_type" id="fixed" value="fixed"
                                                                checked>
                                                            <label class="form-check-label" for="fixed">Fixed</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="discount_type" id="percent" value="Percentage">
                                                            <label class="form-check-label" for="percent">Percentage</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <label class="col-12">Discount</label>
                                                        <div class="col-12">
                                                            <div class="d-flex gap-2">
                                                                <div class="position-relative w-100">
                                                                    <input class="form-control discount-input" type="text" id="discount-amount" name="discount_amount"
                                                                        placeholder="Enter Discount">
                                                                        <i data-feather="percent"></i>
                                                                        <i data-feather="dollar-sign"></i>
                                                                    </div>
                                                                <button type="button" class="btn btn-sm btn-primary" id="apply-discount">Apply</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="contentbox bg-transparent">
                                            <div class="inside p-0">
                                                <div class="contentbox-title">
                                                    <div class="contentbox-subtitle">
                                                        <h3>Payment Summary</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="payment-summery" class="payment-summery">
                                            @includeIf('backend.pos.payment-summery',['cartItems' => $cartItems])
                                        </div>

                                        <div class="radio-buttons-box mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="wallet" value="wallet"
                                                    checked>
                                                <label class="form-check-label" for="wallet">Wallet</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                                                <label class="form-check-label" for="cash">Cash</label>
                                            </div>
                                        </div>
                                        <button class="btn place-btn" type="button" id="checkout-button">place order</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
      @includeIf('backend.pos.booking-model')

    <!-- Booking Modal -->
    <div class="modal fade address-modal" id="newAddress">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title m-0">Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <form id="addressForm" method="post" class="mb-0">
                    <div class="modal-body">
                        <div class="row g-sm-4 g-3">
                        <div class="col-12">
                                <div class="category-list-box">
                                    <label class="label-title" for="role">{{ __('static.address_category') }}</label>
                                    <div class="form-group category-list">
                                        <div class="form-check form-radio">
                                            <input type="radio" name="address_type" id="home" value="Home" class="form-check-input"
                                                @isset($address->type){{ $address->type == 'Home' ? 'checked' : '' }}@endisset
                                                checked>
                                            <label class="form-check-label mb-0 cursor-pointer" for="home">
                                                {{ __('static.home') }}
                                                <span class="check-box"></span>
                                            </label>
                                        </div>
                                        <div class="form-check form-radio">
                                            <input type="radio" name="address_type" id="work" value="Work" class="form-check-input"
                                                @isset($address->type){{ $address->type == 'Work' ? 'checked' : '' }}@endisset>
                                            <label class="form-check-label mb-0 cursor-pointer" for="work">{{ __('static.work') }} <span class="check-box"></span></label>
                                        </div>
                                        <div class="form-check form-radio">
                                            <input type="radio" name="address_type" id="other" value="Other" class="form-check-input"
                                                @isset($address->type){{ $address->type == 'Other' ? 'checked' : '' }}@endisset>
                                            <label class="form-check-label mb-0 cursor-pointer" for="other">{{ __('static.other') }} <span class="check-box"></span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="category-list-box">
                                    <label class="label-title" for="alternative_name">{{ __('static.address.alternative_name') }}</label>
                                    <div class="w-100">
                                        <input class='form-control' type="text" name="alternative_name" id="alternative_name"
                                            value="{{ $address->alternative_name ?? old('alternative_name') }}"
                                            placeholder="{{ __('static.address.enter_alternative_name') }}">
                                        @error('alternative_name')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group row mb-0">
                                    <label class="col-12" for="phone">Phone</label>
                                    <div class="col-12">
                                        <div class="input-group mb-3 phone-detail">
                                            <div class="col-sm-1">
                                                <select class="select-2 form-control select-country-code"
                                                    id="select-country-code" name="code" data-placeholder="">
                                                    @php
                                                        $default = old('code', $customer->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                                                    @endphp
                                                    <option value="" selected></option>
                                                    @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                                                        <option class="option" value="{{ $option->phone_code }}"
                                                            data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                                            @if ($option->phone_code == $default) selected @endif
                                                            data-default="{{ $default }}">
                                                            +{{ $option->phone_code }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('code')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="col-sm-11">
                                                <input class="form-control" type="number" name="phone" id="phone"
                                                    min="1"
                                                    value="{{ isset($customer->phone) ? $customer->phone : old('phone') }}"
                                                    placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                                            </div>
                                        </div>
                                        @error('phone')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="category-list-box">
                                    <label class="label-title" for="address">{{ __('static.users.address') }} <span
                                            class="required-span">*</span></label>
                                    <div class="w-100">
                                        <textarea class="form-control ui-widget autocomplete-google" placeholder="Enter Address " rows="4" id="address"
                                            name="address" cols="50">{{ $address->address ?? old('address') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="category-list-box">
                                    <label class="label-title" for="country">{{ __('static.users.country') }} <span
                                            class="required-span">*</span></label>
                                    <div class="w-100 error-div select-dropdown border-0 p-0 m-0">
                                        <select class="select-2 form-control select-country" id="country_id" name="country_id"
                                            data-placeholder="{{ __('static.users.select_country') }}" required>
                                            <option class="select-placeholder" value=""></option>
                                            @php
                                                $default = old('country_id', @$address->country_id);
                                            @endphp
                                            @foreach ($countries as $key => $option)
                                                <option class="option" value={{ $key }}
                                                    @if ($key == $default) selected @endif data-default="{{ $default }}">
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('country_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="category-list-box">
                                    <label class="label-title" for="state">{{ __('static.users.state') }} <span
                                            class="required-span">*</span></label>
                                    <div class="w-100 error-div select-dropdown border-0 p-0 m-0">
                                        <select class="select-2 form-control select-state"
                                            data-placeholder="{{ __('static.users.select_state') }}" id="state_id" name="state_id"
                                            data-default-state-id="{{ $address->state_id ?? '' }}" required>
                                            <option class="select-placeholder" value=""></option>
                                            @php
                                                $default = old('state_id', @$address->state_id);
                                            @endphp
                                            @if (count($states))
                                                @foreach ($states as $key => $state)
                                                    <option class="option" value={{ $state->id }}
                                                        @if ($state->id == $default) selected @endif data-default="{{ $default }}">
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('state_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="category-list-box">
                                    <label class="label-title" for="branch_name">{{ __('static.city') }} <span
                                            class="required-span">*</span></label>
                                    <div class="w-100">
                                        <input class="form-control" id="city" type="text" name="city"
                                            value="{{ isset($address->city) ? $address->city : old('city') }}"
                                            placeholder="{{ __('static.users.enter_city') }}" required>
                                        @error('city')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-12">
                                <div class="category-list-box">
                                    <label class="label-title" for="area">{{ __('static.area') }} <span
                                            class="required-span">*</span></label>
                                    <div class="w-100">
                                        <input class="form-control" type="text" id="area" name="area"
                                            value="{{ isset($address->area) ? $address->area : old('area') }}"
                                            placeholder="{{ __('static.users.enter_area') }}" required>
                                        @error('area')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div> --}}

                            <div class="col-12">
                                <div class="category-list-box">
                                    <label class="label-title" for="postal_code">{{ __('static.postal_code') }} <span
                                            class="required-span">*</span></label>
                                    <div class="w-100">
                                        <input class="form-control" type="text" id="postal_code" name="postal_code"
                                            value="{{ isset($address->postal_code) ? $address->postal_code : old('postal_code') }}"
                                            placeholder="{{ __('static.users.postal_code') }}" required>
                                        @error('postal_code')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @if(isset($address) && !$address->is_primary)
                                <div class="col-12">
                                    <div class="set-address-box form-group">
                                        <label for="role">{{ __('static.address.set_as_is_primary') }}</label>
                                        <input class="form-check-input checkbox_animated" type="checkbox" name="is_primary" value="1">
                                    </div>
                                </div>
                            @elseif (!isset($address))
                                <div class="col-12">
                                    <div class="set-address-box form-group">
                                        <label for="role">{{ __('static.address.set_as_is_primary') }}</label>
                                        <input class="form-check-input checkbox_animated" type="checkbox" name="is_primary" value="1">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light text-dark" data-bs-target="#bookNowModal"
                            data-bs-toggle="modal">Close</button>
                        <button class="btn btn-primary" type="submit">{{ __('static.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add new use modal -->
    <div class="modal fade add-new-modal" id="addNewModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title m-0">Add New Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group row">
                            <label class="col-12" for="name">Name<span> *</span></label>
                            <div class="col-12">
                                <input class="form-control" type="text" id="name" placeholder="Enter Name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12" for="name">Email<span> *</span></label>
                            <div class="col-12">
                                <input class="form-control" type="mail" id="name" placeholder="Enter Email Address">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12" for="phone">{{ __('static.phone') }}<span> *</span></label>
                            <div class="col-12">
                                <div class="input-group mb-3 phone-detail">
                                    <div class="col-sm-1">
                                        <select class="select-2 form-control select-country-code"
                                            id="select-country-code" name="code" data-placeholder="">
                                            @php
                                                $default = old('code', $customer->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                                            @endphp
                                            <option value="" selected></option>
                                            @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                                                <option class="option" value="{{ $option->phone_code }}"
                                                    data-image="{{ asset('admin/images/flags/' . $option->flag) }}" @if ($option->phone_code == $default) selected @endif
                                                    data-default="{{ $default }}">
                                                    +{{ $option->phone_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('code')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-sm-11">
                                        <input class="form-control" type="number" name="phone" id="phone"
                                            value="{{ isset($customer->phone) ? $customer->phone : old('phone') }}"
                                            min="1" placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                                    </div>
                                </div>
                                @error('phone')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModal"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-start">
                    <div class="main-img">
                        <i data-feather="trash-2"></i>
                    </div>
                    <div class="text-center">
                        <div class="modal-title"> {{ __('static.delete_message') }}</div>
                        <p class="mb-0">{{ __('static.delete_note') }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel multi-delete-cancel" id="cancelModalBtn"
                        data-dismiss="modal">{{ __('static.cancel') }}</button>
                    <button type="button" class="btn btn-primary delete spinner-btn"
                        id="confirm-DeleteRows">{{ __('static.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('admin/js/swiper-slider.js') }}"></script>
    <script src="{{ asset('admin/js/flatpickr.js') }}"></script>
    <script src="{{ asset('admin/js/custom-flatpickr.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_map_api_key') }}&libraries=places"></script>

    <script>
    $(document).ready(function() {

        "use strict";

        $('.select-2').select2();
        $('.select-2').on('select2:close', function (e) {
            $(this).valid();
        });
        var categorySlider = new Swiper(".category-slider", {
            slidesPerView: 6.5,
            spaceBetween: 15,
            navigation: {
                nextEl: ".category-button-next",
                prevEl: ".category-button-prev",
            },
            breakpoints: {
                0: {slidesPerView: 1.8, spaceBetween: 15},
                360: {slidesPerView: 2, spaceBetween: 15},
                600: {slidesPerView: 3.2, spaceBetween: 15},
                790: {slidesPerView: 4, spaceBetween: 15},
                1045: {slidesPerView: 5.2, spaceBetween: 15},
                1199: {slidesPerView: 6.4, spaceBetween: 15},
                1230: {slidesPerView: 4.3, spaceBetween: 15},
                1590: {slidesPerView: 5, spaceBetween: 15},
                1920: {slidesPerView: 6.5, spaceBetween: 15}
            },
        });

        $('#providerDropdown').on('change', function () {
            let providerId = $(this).val();
            let search = $('#service-search').val();

                $.ajax({
                    url: "{{ route('backend.booking.filter-services') }}",
                    type: "GET",
                    data: { provider_id: providerId , search : search },
                    success: function (response) {
                        $('#serviceListContainer').html(response.html);
                        $('.nav-link').removeClass('active');
                        $('.tab-pane').removeClass('show active');
                        let activeIndex = response.activeIndex || 0;
                        $('#tab-' + activeIndex).addClass('active');
                        $('#category-' + activeIndex).addClass('show active');
                    }
                });
        });

        $('.booking-modal').on('show.bs.modal', function () {
            let $modal = $(this);
            let serviceId = $modal.attr('id').split('-')[1];
            let providerType = $('#providerDropdown option:selected').data('provider-type');
            let input = $modal.find('input[name="required_servicemen"]');
            let addBtn = $modal.find('.add');
            let minusBtn = $modal.find('.minus');
            
            if(providerType === 'freelancer'){
                input.prop('readonly', true).val(1); 
                addBtn.prop('disabled', true);
                minusBtn.prop('disabled', true);
            } else {
                input.prop('readonly', false);
                addBtn.prop('disabled', false);
                minusBtn.prop('disabled', false);
            } 
        });

        $('#service-search').on('change', function () {
            let search = $(this).val();
            let providerId = $('#providerDropdown').val();

            $.ajax({
                url: "{{ route('backend.booking.filter-services') }}",
                type: "GET",
                data: { search: search , provider_id : providerId },
                success: function (response) {
                    $('#serviceListContainer').html(response.html);

                    $('.nav-link').removeClass('active');
                    $('.tab-pane').removeClass('show active');
                    let activeIndex = response.activeIndex || 0;
                    $('#tab-' + activeIndex).addClass('active');
                    $('#category-' + activeIndex).addClass('show active');
                }
            });
        });

        $('#apply-discount').on('click', function () {
            let discountAmount = parseFloat($('#discount-amount').val());
            let discountType = $('input[name="discount_type"]:checked').val();
            let currencySymbol = '{{ Helpers::getDefaultCurrency()->symbol }}';

            let subtotal = parseFloat($('#subtotal-value').text().replace(currencySymbol, '').trim());
            let totalTax = parseFloat($('#tax-value').text().replace(currencySymbol, '').trim());
            let originalTotal = subtotal + totalTax;

            if (isNaN(discountAmount) || discountAmount < 0) {
                alert('Enter a valid discount');
                return;
            }

            let discount = 0;

            if (discountType.toLowerCase() === 'fixed') {
                discount = discountAmount;
            } else if (discountType.toLowerCase() === 'percentage') {
                discount = (originalTotal * discountAmount) / 100;
            }

            discount = Math.min(discount, originalTotal);

            let newTotal = originalTotal - discount;

            $('#discount-value').text(currencySymbol + discount.toFixed(2));
            $('#total-value').text(currencySymbol + newTotal.toFixed(2));
        });


        $('.booking-modal').each(function () {
            let modal = $(this);
            let serviceId = modal.attr('id').split('-')[1];
            let input = $('#quantityInput-' + serviceId);
            let addButton = $('#add-' + serviceId);
            let minusButton = $('#minus-' + serviceId);

            addButton.on('click', function () {
                let val = parseInt(input.val(), 10);

                if (val < parseInt(input.attr('max'), 10)) {
                    input.val(val + 1);
                }
            });

            minusButton.on('click', function () {
                let val = parseInt(input.val(), 10);
                if (val > parseInt(input.attr('min'), 10)) {
                    input.val(val - 1);
                }
            });
        });


        $('#consumerDropdown').on('change' , function () {
            let consumerId = $(this).val();
                $.ajax({
                    url: "{{ route('backend.booking.get-addresses') }}",
                    type: "GET",
                    data: {consumer_id : consumerId },
                    success: function (response) {
                        $('.consumer-addresses-list').html(response.html);
                    }
                });
        })

        $('.submit-booking-button').on('click', function () {
            let $modal = $(this).closest('.modal');
            let providerType = $('#providerDropdown option:selected').data('provider-type');
            let requiredServicemen = $modal.find('input[name="required_servicemen"]').val();
            let addressId = $modal.find('input[name="address_id"]:checked').val();
            let dateTime = $modal.find('input[name="date_time"]').val();
            let servicemanDropdown = $modal.find('select[name="serviceman_id[]"]').val();
            let customMessage = $modal.find('textarea[name="custom_message"]').val();
            let consumerId = $('#consumerDropdown').val();

            if (!consumerId) {
                toastr.error('Please select a consumer.');

                return
            }

            if (!requiredServicemen || requiredServicemen <= 0) {
                toastr.error('Please specify the required number of servicemen.');
                return
            }

            if (!addressId) {
                toastr.error('Please select an address.');
                return
            }

            if (!dateTime) {
                toastr.error('Please select a date and time.');
                return
            }

            if ((!servicemanDropdown || servicemanDropdown.length === 0) && providerType === 'company') {
                toastr.error('Please select at least one serviceman.');
                return
            }

            if ((!servicemanDropdown || servicemanDropdown.length > requiredServicemen) && providerType === 'company') {
                toastr.error('you can not select more than required serviceman');
                return
            }

            let formData = $modal.find('form').serialize();
            formData += `&consumer_id=${consumerId}`;
            let serviceId = $modal.attr('id').split('-')[1];
            let $csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('backend.service.booking') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $csrfToken
                },
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        $('#bookNowModal-' + response.service_id).modal('hide');
                        $('#cart-list-box').html(response.html);
                        $('#payment-summery').html(response.payment_summery);

                        toastr.success("Booking successfully submitted!");
                    }
                },
                error: function(xhr) {
                    toastr.error("An error occurred while processing your request.");
                }
            });
        });


        $("#checkoutForm").validate({
            ignore: [],
            rules: {
                "payment_method" : "required",
            },
            messages: {
            }
        });


        $('#checkout-button').on('click', function () {

            let paymentMethod = $('input[name="payment_method"]:checked').val();
            let consumerId = $('#consumerDropdown').val();


            if (!consumerId) {
                toastr.error('Please select a consumer.');
                return
            }
            if (!paymentMethod) {
                toastr.error('Please select payment method.');
                return
            }

            let formData = $('#checkoutForm').serialize();

            let $csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('backend.service.checkout') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $csrfToken
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                        console.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("An unknown error occurred.");
                    }

                }
            });
        });


        $('#confirm-DeleteRows').on('click', function () {

            let formData = $('#checkoutForm').serialize();

            let $csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route('backend.cart.clear') }}',
                type: 'delete',
                data: {
                    _method: 'DELETE',
                },
                headers: {
                    'X-CSRF-TOKEN': $csrfToken
                },
                success: function(response) {

                    window.location.reload();
                }
            });
        });

        $('.remove-from-cart').on('click', function () {
            let itemId = $(this).data('id');
            let $csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (!confirm("Are you sure you want to remove this item?")) {
                return;
            }
            let url =  "{{ route("backend.cart.remove" , '') }}" + "/" + itemId
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {},
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $csrfToken
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#cart-list-box').html(response.html);

                    } else {

                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("An error occurred. Please try again.");
                }
            });
        });

        $('#addressForm').on('submit' , function(e) {
            e.preventDefault();
            let consumerId = $('#consumerDropdown').val();

            if (!consumerId) {
                toastr.error('Please select a consumer.');
                return
            }

            var formData = $(this).serialize();
            formData += `&consumer_id=${consumerId}`;
            let $csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{ route('backend.address.add') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $csrfToken
                },
                success: function(response) {
                    $('.consumer-addresses-list').html(response.html);
                    $('#newAddress').modal('hide');

                    toastr.success("Address created sucessfully");
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                        console.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("An unknown error occurred.");
                    }

                }
            });

        })


        $('.select-country').on('change', function() {
            var idCountry = $(this).val();
            populateStates(idCountry);
        });

        function populateStates(countryId, state) {
            $(".select-state").html('');
            $.ajax({
                url: "{{ url('/states') }}",
                type: "POST",
                data: {
                    country_id: countryId,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(result) {
                    $('.select-state').html('<option value="">Select State</option>');
                    $.each(result.states, function(key, value) {
                       $('.select-state').append(
                                    `<option value="${value.id}" ${value.id === state ? 'selected' : ''}>${value.name}</option>`
                                );
                    });
                    var defaultStateId = $(".select-state").data("default-state-id");
                    if (defaultStateId !== '') {
                        $('.select-state').val(defaultStateId);
                    }
                }
            });
        }

        function initializeGoogleAutocomplete() {

            $(".autocomplete-google").each(function() {
                var autocomplete = new google.maps.places.Autocomplete(this);


                autocomplete.addListener("place_changed", function() {
                    var place = autocomplete.getPlace();
                    if (!place.place_id) {
                        console.log("No place details available");
                        return;
                    }

                    var placeId = place.place_id;
                    getAddressDetails(placeId);
                });
            });
        }

        function getAddressDetails(placeId) {
            $.ajax({
                url: "/backend/google-address",
                type: 'GET',
                dataType: "json",
                data: {
                    placeId: placeId,
                },
                success: function(data) {
                    console.log("address data", data.location)
                    $('#latitude').val(data.location.lat);
                    $('#longitude').val(data.location.lng);
                    $('#lat').val(data.location.lat);
                    $('#lng').val(data.location.lng);

                    $('#city').val(data.locality);
                    $('#postal_code').val(data.postal_code);
                    $('#postal_code').val(data.postal_code);
                    var street = '';
                    if (data.streetNumber) {
                        street += data.streetNumber + ", ";
                    }

                    if (data.streetName) {
                        street += data.streetName + ", ";
                    }
                    $('#street_address_1').val(street);
                    $('#area').val(data.area);
                    var countryId = data.country_id;
                    if (countryId) {
                        $('#country_id').val(countryId).trigger('change');
                    }

                    var stateId = data.state_id;
                    if (stateId) {
                        console.log("called");
                        $('.select-state').attr('data-default-state-id', stateId);
                        $('.select-state').val(stateId).trigger('change');
                        populateStates(countryId, stateId);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log("AJAX error in getAddressDetails:", textStatus,
                        errorThrown);
                }
            });
        }

        initializeGoogleAutocomplete();

        $("#addressForm").validate({
            ignore: [],
            rules: {
                "country_id": "required",
                "state_id": "required",
                "city": "required",
                // "area": "required",
                "postal_code": "required",
                "address": "required"
            }
        });

        const maxBookingDays = {{ Helpers::getsettings()['default_creation_limits']['max_booking_days'] ?? 30 }};
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + maxBookingDays);

                flatpickr("#date-time", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today",
                    maxDate: maxDate,
                });
        });

    </script>
@endpush
