@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')
@extends('frontend.layout.master')
@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/jquery-datetimepicker/jquery.datetimepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('frontend/css/vendors/xdshoft-datetimerpicker/xdsoft-picker.css') }}" />
@endpush

@php
    $maxSelected = session('cart')['required_servicemen'];
    $perServicemenCharge = Helpers::getPerServicemen($cartItem['service']); 
@endphp

@section('title', __('frontend::static.bookings.service_boking'))

@section('breadcrumb')
    <nav class="breadcrumb breadcrumb-icon">
        <a class="breadcrumb-item" href="{{ route('frontend.home') }}">{{ __('frontend::static.bookings.home') }}</a>
        <a class="breadcrumb-item" href="{{ route('frontend.service.index') }}">{{ __('frontend::static.bookings.services') }}</a>
        <a class="breadcrumb-item">{{ __('frontend::static.bookings.service_booking') }}</a>
    </nav>
@endsection

@section('content')
    <!-- Service List Section Start -->
    <section class="section-b-space service-section">
        <div class="container-fluid-lg">
            <div class="row g-4">
                <div class="col-xxl-8 col-xl-7">
                    <div class="cart basic-wizard important-validation br-10">
                        <form id="serviceBookingForm" action="{{ route('frontend.booking.service.store') }}"
                            method="POST">
                            @csrf
                            <div class="stepper-horizontal" id="stepper1">
                                <div class="step-heading stepper-one stepper step editing" data-step="1">
                                    <div class="step-circle"><span>01</span></div>
                                    <div class="step-title">{{ __('frontend::static.bookings.book_date_and_time') }}</div>
                                </div>
                                <div class="step-heading stepper-two step" data-step="2">
                                    <div class="step-circle"><span>02</span></div>
                                    <div class="step-title">{{ __('frontend::static.bookings.bill_summary') }}</div>
                                </div>
                            </div>
                            <div id="msform">
                                <input type="hidden" name="service_id" value="{{ $cartItem['service']?->id }}" />
                                <div class="step-content stepper-one needs-validation custom-input" id="step1Div" data-step="1">
                                    <div class="py-0 service-booking">
                                        <ul>
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>

                                                <div class="booking-data">
                                                    @includeIf('frontend.booking.select-address')
                                                </div>
                                                @error('address_id')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </li>
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <h3 class="mb-2">
                                                        {{ __('frontend::static.bookings.date_and_time') }}</h3>
                                                    <div class="date-time-picket-sec">
                                                        <div class="select-option">
                                                            <div class="form-check mb-0">
                                                                <input type="radio" id="customTimeSlot01" value="custom" name="select_date_time_2" checked class="form-radio-input custom_date_time_1">
                                                                <label for="customTimeSlot01">{{ __('frontend::static.bookings.custom_date_time') }}</label>
                                                            </div>
                                                            
                                                            <div class="d-flex align-items-start gap-sm-3 gap-2">
                                                                <div class="form-check mb-0">
                                                                    <input type="radio" data-bs-toggle="modal" data-bs-target="#datetimeModal" id="providerTimeSlot01" value="timeslot" name="select_date_time_2" class="form-radio-input custom_date_time_1">
                                                                    <label for="providerTimeSlot01">{{ __('frontend::static.bookings.as_per_provider_time_slot') }}</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="date-time-picker">
                                                            <div class="input-group flatpicker-calender">
                                                                <input class="form-control form-control-gray datetime-local" id="datetime-local" type="text" readonly="readonly" placeholder="Select Date">
                                                                <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                            </div>

                                                            <div class="input-group flatpicker-calender">
                                                                <input class="form-control form-control-gray time-local" id="time-picker" type="time" placeholder="Select time">
                                                                <i class="iconsax input-icon" icon-name="clock"></i>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="date_time" id="dateTimeInput" value="">
                                                    </div>
                                                </div>
                                                @error('date_time')
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </li>
                                            @isset($cartItem['service'])
                                                @if ($cartItem['select_serviceman'] == 'as_per_my_choice')
                                                    <li class="d-flex align-items-start booking-list">
                                                        <div class="activity-dot"></div>
                                                        <div class="booking-data">
                                                            <div class="select-servicemen-div selectServicemenDiv"
                                                                id="selectServicemenDiv">
                                                                <h3>{{ __('frontend::static.bookings.select_servicemen') }}
                                                                </h3>
                                                                <button id="selectServicemenBtn" type="button"
                                                                    class="servicemen-lists mt-2" data-bs-toggle="modal"
                                                                    data-bs-target="#checkservicemenListModal">
                                                                    +
                                                                    {{ __('frontend::static.bookings.select_servicemen') }}
                                                                </button>
                                                            </div>
                                                            <div class="selected-servicemen-div selectedServicemenDiv"
                                                                id="selectedServicemenDiv" style="display:none;">
                                                                <div
                                                                    class="d-flex align-items-center gap-1 justify-content-between mb-2">
                                                                    <h3>{{ __('frontend::static.bookings.select_servicemen') }}
                                                                    </h3>
                                                                    <button type="button" class="edit-option"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#checkservicemenListModal">{{ __('frontend::static.bookings.edit') }}
                                                                    </button>
                                                                </div>
                                                                <div class="selected-men">

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="required_servicemen"
                                                            value="{{ $maxSelected }}" />
                                                        <input type="hidden" name="select_serviceman"
                                                            value="{{ $cartItem['select_serviceman'] }}" />
                                                    </li>
                                                @endif
                                            @endisset

                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <h3 class="mb-2">{{ __('frontend::static.bookings.custom_message') }}</h3>
                                                    <textarea class="form-control form-control-white" name="description" placeholder="Write here.." rows="3"></textarea>
                                                    <p class="mb-4 mt-2 text-light">{{ __('frontend::static.bookings.service_booking_note') }}</p>
                                                </div>
                                            </li>
                                            <!-- Additional Services Section -->
                                            @isset($cartItem['additional_services'])
                                                @if ($cartItem['service'] && $cartItem['additional_services'])
                                                    <div>
                                                        <h4 class="service-title">{{ __('frontend::static.modal.add_ons') }}</h4>
                                                        <div class="select-additional">
                                                            @foreach ($cartItem['additional_services'] as $index => $addOn)
                                                                @php
                                                                    $additionalService = Helpers::getAdditionalServiceById($addOn['id']);
                                                                @endphp
                                                                
                                                                <div class="form-check">
                                                                    <input type="checkbox" id="additional-{{ $additionalService->id }}" name="additional_services[{{ $index }}][id]" value="{{ $additionalService->id }}" class="form-check-input" checked>
                                                                    <input type="hidden" name="additional_services[{{ $index }}][qty]" value="{{ $addOn['qty'] }}">
                                                                    @php
                                                                        $totalAddOnPrice = $additionalService->price * $addOn['qty']
                                                                    @endphp
                                                                    <label for="additional-{{ $additionalService->id }}">
                                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                            {{ $additionalService->title }} - {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($additionalService->price) }}
                                                                        @else
                                                                            {{ $additionalService->title }} - {{ Helpers::covertDefaultExchangeRate($additionalService->price) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                                        @endif
                                                                        <div class="additional">
                                                                            <div for="additional-{{ $additionalService->id }}">
                                                                               {{ __('static.service.qty') }} {{ $addOn['qty'] }} 
                                                                            </div>
                                                                            <div>
                                                                                {{ __('static.service.add_on_total_price') }}
                                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                                    {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($totalAddOnPrice) }}
                                                                                @else
                                                                                    {{ Helpers::covertDefaultExchangeRate($totalAddOnPrice) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endisset
                                        </ul>
                                    </div>
                                </div>
                                <div class="step-content stepper-two needs-validation custom-input" data-step="2" id="step2Div" style="display: none;">
                                    <div class="py-0 service-booking">
                                        <ul>
                                            @isset($cartItem['service'])
                                                @if ($cartItem['select_serviceman'] == 'as_per_my_choice')
                                                    <input type="hidden" id="serviceman_id" name="serviceman_id" value="" />
                                                    <li class="d-flex align-items-start booking-list">
                                                        <div class="activity-dot"></div>
                                                        <div class="booking-data">
                                                            <div class="select-servicemen-div selectServicemenDiv" id="selectServicemenDiv">
                                                                <h3>{{ __('frontend::static.bookings.select_servicemen') }}
                                                                </h3>
                                                                <button id="selectServicemenBtn" type="button" class="servicemen-lists mt-2" data-bs-toggle="modal" data-bs-target="#checkservicemenListModal">
                                                                    +{{ __('frontend::static.bookings.select_servicemen') }}
                                                                </button>
                                                            </div>
                                                            <div class="selected-servicemen-div selectedServicemenDiv" id="selectedServicemenDiv" style="display:none;">
                                                                <div class="d-flex align-items-center gap-1 justify-content-between mb-2">
                                                                    <h3>{{ __('frontend::static.bookings.selected_servicemen') }}
                                                                    </h3>
                                                                    <button type="button" class="edit-option" data-bs-toggle="modal" data-bs-target="#checkservicemenListModal">{{ __('frontend::static.bookings.edit') }}</button>
                                                                </div>
                                                                <div class="selected-men">

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @else
                                                    <li class="d-flex align-items-start booking-list">
                                                        <div class="activity-dot"></div>
                                                        <div class="booking-data">
                                                            <h3>{{ __('frontend::static.bookings.service_delivery_location') }}</h3>
                                                            <div class="input-group">
                                                                <input class="form-control form-control-white" value="As you previously said, the app will select your servicemen." type="text">
                                                                <i class="iconsax input-icon" icon-name="info-circle"></i>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endisset
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <h3 class="mb-2">Date and Time</h3>
                                                    <p class="text-light mb-2">{{ __('frontend::static.bookings.take_around') }}</p>

                                                    <div class="date-time-picket-sec">
                                                        <div class="select-option">
                                                            <div class="form-check">
                                                                <input type="radio" id="customTimeSlot" value="custom" name="select_date_time" class="form-radio-input" checked>
                                                                <label for="customTimeSlot">{{ __('frontend::static.bookings.custom_date_time') }}</label>
                                                            </div>
                                                            <div class="d-flex align-items-start gap-sm-3 gap-2">
                                                                <div class="form-check">
                                                                    <button type="button" class="time-slot" data-bs-toggle="modal" data-bs-target="#datetimeModal">
                                                                        <input type="radio" value="timeslot" id="providerTimeSlot" name="select_date_time" class="form-radio-input select_time_slot_2">
                                                                    </button>
                                                                </div>
                                                                <label for="providerTimeSlot">{{ __('frontend::static.bookings.as_per_provider_time_slot') }}</label>
                                                            </div>
                                                            @error('select_date_time')
                                                                <span class="invalid-feedback d-block" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>

                                                        <div class="date-time-picker">
                                                            <div class="input-group flatpicker-calender">
                                                                <input class="form-control form-control-gray datetime-local datetime-local-1" id="datetime-local" type="text" readonly="readonly" placeholder="Select Date">
                                                                <i class="iconsax input-icon" icon-name="calendar-1"></i>
                                                            </div>
                                                            <div class="input-group">
                                                                <input class="form-control form-control-gray time-picker time-local-1" id="time-picker" type="time" placeholder="Select time">
                                                                <i class="iconsax input-icon" icon-name="clock"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex align-items-start booking-list">
                                                <div class="activity-dot"></div>
                                                <div class="booking-data">
                                                    <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-start justify-content-between gap-1 mb-2">
                                                        <h3 class="mb-0">{{ __('frontend::static.bookings.bill_summary') }}</h3>
                                                        @auth
                                                            <span class="text-success">{{ __('frontend::static.bookings.wallet') }}
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <small>: {{ Helpers::getDefaultCurrencySymbol() }}{{ auth()?->user()?->wallet?->balance }}</small>
                                                                @else
                                                                    <small>: {{ auth()?->user()?->wallet?->balance }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                                                @endif
                                                            </span>
                                                        @endauth
                                                    </div>

                                                    @php
                                                        $perAmount = Helpers::covertDefaultExchangeRate(
                                                            $perServicemenCharge,
                                                        );
                                                        $total = $perAmount * $maxSelected;
                                                        $symbol = Helpers::getDefaultCurrencySymbol();
                                                        $additionalTotal = 0;
                                                        $additionalCount = 0;
                                                    @endphp
                                                    @isset($cartItem['additional_services'])
                                                        @foreach ($cartItem['additional_services'] as $addOn)
                                                            @php
                                                                $additionalService = Helpers::getAdditionalServiceById($addOn['id']);
                                                                $addOnTotalPrice = $additionalService->price * $addOn['qty'];
                                                                $additionalTotal += Helpers::covertDefaultExchangeRate($addOnTotalPrice);
                                                                $additionalCount++;
                                                            @endphp
                                                        @endforeach
                                                    @endisset
                                                    <div class="bill-summary">
                                                        <ul class="charge">
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.service_price') }}</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($cartItem['service']['price']) }}</span>
                                                                @else
                                                                    <span>{{ Helpers::covertDefaultExchangeRate($cartItem['service']['price']) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                                @endif
                                                            </li>
                                                            @if($cartItem['service']['discount'] > 0) 
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.service_discount') }}</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ $cartItem['service']['discount'] ?? 0 }}</span>
                                                                @else
                                                                    <span>{{ $cartItem['service']['discount'] ?? 0 }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                                @endif
                                                            </li>
                                                            @endif
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.service_rate') }}</p>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <span>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($cartItem['service']['service_rate']) }}</span>
                                                                @else
                                                                    <span>{{ Helpers::covertDefaultExchangeRate($cartItem['service']['service_rate']) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                                @endif
                                                            </li>
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.per_servicemen_charge') }}</p>
                                                                <span>{{ $symbol }}{{ $perAmount }}</span>
                                                            </li>
                                                            <li>
                                                                <p>{{ $maxSelected }}{{ __('frontend::static.bookings.servicemen') }}({{ $symbol }} {{ $perAmount }}.00*{{ $maxSelected }})</p>
                                                                <span>{{ $symbol }} {{ $total }}</span>
                                                            </li>
                                                            <li>
                                                                <p>{{ isset($cartItem['additional_services']) ? $additionalCount : 0 }}{{ __('frontend::static.bookings.add_ons') }}</p>
                                                                <span>{{ $symbol }}{{ isset($cartItem['additional_services']) ? number_format($additionalTotal, 2) : '0.00' }}</span>
                                                            </li>
                                                            {{-- <li>
                                                                <p>{{ __('frontend::static.bookings.tax') }}</p>
                                                                <span>{{ __('frontend::static.bookings.cost_at_checkout') }}</span>
                                                            </li> --}}
                                                        </ul>
                                                        <ul class="total">
                                                            @php
                                                                $grandTotal = $total + $additionalTotal;
                                                            @endphp
                                                            <li>
                                                                <p>{{ __('frontend::static.bookings.total_amount') }}</p>
                                                                <span>{{ $symbol }} {{ $grandTotal }}</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="dashed-border"></div>
                                                    <div class="danger-note">
                                                        <h3>
                                                            {{ __('frontend::static.bookings.cancellation_policy') }}
                                                        </h3>
                                                        <p class="m-0">
                                                            {{ __('frontend::static.bookings.cancellation_policy_note') }}
                                                        </p>
                                                    </div>

                                                    <div class="note">
                                                        <label>{{ __('frontend::static.bookings.disclaimer') }}</label>
                                                        <p>{{ __('frontend::static.bookings.disclaimer_note') }}</p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="wizard-footer service-booking-footer">
                                <button type="button" class="btn btn-outline" id="backbtn" style="display: none;">{{ __('frontend::static.bookings.back') }}</button>
                                <button type="button" class="btn btn-solid next-btn" id="nextbtn">{{ __('frontend::static.bookings.next_step') }}</button>
                                <button type="submit" class="btn btn-solid spinner-btn" id="confirmBookingBtn" style="display: none;">{{ __('frontend::static.bookings.confirm_booking') }}<span class="spinner-border spinner-border-sm" style="display: none;"></span></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-xxl-4 col-xl-5">
                    <div class="service-right-box">
                        @php
                            $cart = session('service_bookings', []);
                        @endphp
                        <div class="cart br-10">
                            <div class="cart-header">
                                <h3 class="mb-0 f-w-600">{{ __('frontend::static.bookings.your_cart') }}</h3>
                                <span>({{ count($cart) }} {{ __('frontend::static.bookings.item_added') }})</span>
                            </div>
                            <div class="cart-body">
                                @forelse($cart as $serviceItem)
                                    @php
                                        $service = Helpers::getServiceById($serviceItem['service_id']);
                                        $provider = Helpers::getProviderById($service?->user_id);
                                    @endphp
                                    <div class="cart-item">
                                        <div class="cart-heading">
                                            <div class="cart-title">
                                    @php
                                       $media = $service->getFirstMedia('images');
                                     @endphp

                                    @if($media)
                                         <img src="{{ $media->getUrl() }}" alt="{{ $service->name }}">
                                        @else
                                        <img src="{{ asset('frontend/images/default-service.jpg') }}" alt="{{ $service->name }}" class="img-fluid">
                                    @endif

                                                <div>
                                                    <p class="mb-1">{{ $provider?->name }}</p>
                                                    <div class="rate">
                                                        <img src="{{ asset('frontend/images/svg/star.svg') }}"
                                                            alt="star" class="img-fluid star">
                                                        <small>{{ $provider?->review_ratings }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart-detail">
                                            <div class="selected-service">
                                                <img src="{{ $service?->web_img_thumb_url }}"
                                                    alt="{{ $service?->title }}" class="mw-80">
                                                <div class="service-info">
                                                    <h3>{{ $service?->title }}</h3>
                                                    <div class="mt-1">
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            <span class="price">{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</span>
                                                        @else
                                                            <span class="price">{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</span>
                                                        @endif
                                                        @if ($service?->discount)
                                                            <small class="discount">({{ $service?->discount }}% off)</small>
                                                        @endif
                                                    </div>
                                                    @if ($serviceItem['date_time'])
                                                        <ul class="date">
                                                            <li class="d-flex align-items-center gap-1">
                                                                <i class="iconsax" icon-name="calendar-1"></i>
                                                                <span>{{ \Carbon\Carbon::parse($serviceItem['date_time'])?->format('j F, Y') }}</span>
                                                            </li>
                                                            <li class="d-flex align-items-center gap-1">
                                                                <i class="iconsax" icon-name="clock"></i>
                                                                <span>{{ \Carbon\Carbon::parse($serviceItem['date_time'])?->format('g:i A') }}</span>
                                                            </li>
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($cartItem['select_serviceman'] == 'user_choose')
                                                <ul class="date-time pt-3">
                                                    <li>
                                                        <span>{{ __('frontend::static.bookings.selected_servicemen') }}</span>
                                                        <small
                                                            class="text-primary">{{ $cartItem['required_servicemen'] }}
                                                            {{ __('frontend::static.bookings.servicemen') }}</small>
                                                    </li>
                                                </ul>
                                            @endif
                                            <div class="note m-0">
                                                <p class="mt-1">
                                                    {{ __('frontend::static.bookings.user_choose_note') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center">
                                        <div class="cart-img my-5">
                                            <img src="{{ asset('frontend/images/cart/1.png') }}" alt="no cart">
                                        </div>
                                        <div class="no-cart-found">
                                            <h3>{{ __('frontend::static.bookings.oops_nothing_added') }}</h3>
                                            <p class="text-light">
                                                {{ __('frontend::static.bookings.nothing_item_cart') }}</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Service List Section End -->

    <!-- Start Add Address Modal -->
    <div class="modal fade address-modal" id="locationModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('frontend.address.store') }}" id="addressForm" method="post">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="addaddressModalLabel">{{ __('static.address.add') }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        @include('frontend.address.fields')
                    </div>
                    <div class="modal-footer pt-0">
                        <button type="button" class="btn btn-outline"
                            data-bs-dismiss="modal">{{ __('frontend::static.bookings.close') }}</button>
                        <button type="submit"
                            class="btn btn-solid submitBtn spinner-btn">{{ __('frontend::static.bookings.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @includeIf('frontend.inc.modal')

    <!-- Date Time modal -->
    <div class="modal fade date-time-modal" id="datetimeModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="datetimeModalLabel">{{ __('frontend::static.bookings.select_provider_date_time_slot') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0">
                    <div class="row g-3">
                        <div class="date-time-slot-box">
                            <input id="datetimepicker" type="date" class="form-control flatpicker-calender" placeholder="Select Date" />
                        </div>
                        <div class="col-12">
                            <div id="timeSlotsContainer" class="time-slot-main-box"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-block">
                    {{-- <div class="inline-picker-btn w-100"> --}}
                    <button type="button" id="providerDateTimeBtn" class="btn btn-solid">{{ __('frontend::static.bookings.select_date_time') }}</button>
                    {{-- </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Booking Form js -->
    <script src="{{ asset('frontend/js/form-wizard.js/booking-form.js') }}"></script>

    <script src="{{ asset('frontend/js/xdshoft-datetimerpicker/xdsoft-picker.js') }}"></script>
    <script src="{{ asset('frontend/js/xdshoft-datetimerpicker/xdsoft-picker-2.js') }}"></script>

    <!-- Flat-picker js -->
    <script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
    {{-- <script src="{{ asset('frontend/js/flat-pickr/custom-flatpickr.js') }}"></script> --}}
    <script>
            const maxBookingDays = {{ Helpers::getsettings()['default_creation_limits']['max_booking_days'] ?? 30 }};
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + maxBookingDays);
                
                function getMinTime() {
                    const now = new Date();
                    now.setHours(now.getHours() + 2);
                    return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                flatpickr("#datetimepicker", {
                    dateFormat: "d-m-Y",
                    minDate: "today",
                    maxDate: maxDate,
                    placeholder: "Select Date",
                    disableMobile: true,
                });

                flatpickr("#time-picker", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minTime: getMinTime(),
                   placeholder: "Select Time",
                    disableMobile: true,
                });
                
                $(".datetime-local").each(function() {
                flatpickr(this, {
                    dateFormat: "Y-m-d",
                    minDate: new Date(),
                    maxDate: maxDate,
                    placeholder: "Select Date",
                    disableMobile: true,
                });
            });
        const providerTimeSlot = @json($providerTimeSlot);
        document.getElementById('datetimepicker').addEventListener('change', function () {
            const [day, month, year] = this.value.split('-');
            const selectedDate = new Date(`${year}-${month}-${day}`);
            const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase();
            const daySlot = providerTimeSlot.time_slots.find(slot => slot.day === dayName && slot.is_active === 1);
            const container = document.getElementById('timeSlotsContainer');
            container.innerHTML = '';

            if (daySlot && daySlot.slots.length > 0) {
                daySlot.slots.forEach(time => {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-outline-primary';
                    btn.textContent = time;
                    btn.setAttribute('type', 'button');
                    btn.setAttribute('data-time', time);
                    container.appendChild(btn);
                });
            } else {
                container.innerHTML = '<p class="no-data">No slots available for this day.</p>';
            }
            
        });

        $(document).on('click', '#timeSlotsContainer button', function () {
            $('#timeSlotsContainer button').removeClass('active');
            $(this).addClass('active');
            selectedSlotTime = $(this).data('time');
        });

        // On clicking select button
        $('#providerDateTimeBtn').click(function () {
            const selectedDateStr = $('#datetimepicker').val(); 
            const [day, month, year] = selectedDateStr.split('-');
            const selectedDateObj = new Date(`${year}-${month}-${day}`);

            if (!selectedDateObj || !selectedSlotTime) {
                alert("Please select a date and time slot.");
                return;
            }

            const formattedDate = selectedDateObj.toISOString().split('T')[0]; 
            const formattedTime = selectedSlotTime; 

            // Set values to fields
            $('.datetime-local, .datetime-local-1').val(formattedDate);
            $('.time-local, .time-local-1').val(formattedTime);
            $('#dateTimeInput').val(`${formattedDate} ${formattedTime}`);
            $('#datetimeModal').modal('hide');
        });

    </script>

    <script>        
        $(document).ready(function() {

            // Initialize form validation
            $("#serviceBookingForm").validate({
                ignore: [], // Don't ignore any fields, including hidden ones
                rules: {
                    'address_id': {
                        required: true
                    },
                    'select_date_time': {
                        required: true
                    },
                    'date_time': {
                        required: function() {
                            return $('input[name="select_date_time"]:checked').val() === 'custom';
                        }
                    },
                    'select_serviceman': {
                        required: function() {
                            return $('#selectServicemenDiv').is(':visible');
                        }
                    },
                    'serviceman_id': {
                        required: function() {
                            return $('#selectServicemenDiv').is(':visible');
                        }
                    },
                    'description': {
                        required: false
                    }, // Description is optional
                },
                messages: {
                    'address_id': {
                        required: "Please select an address"
                    },
                    'select_date_time': {
                        required: "Please choose a date and time"
                    },
                    'date_time': {
                        required: "Please select a date and time"
                    },
                    'select_serviceman': {
                        required: "Please select a serviceman"
                    },
                    'serviceman_id': {
                        required: "Please select a serviceman"
                    },
                },
                submitHandler: function(form) {
                    // Handle the form submission (if needed)
                    form.submit();
                }
            });
            // Handle Next button click
            $('#nextbtn').on('click', function() {
                let selectedValue = $('input[name="select_date_time_2"]:checked').val();
                $('input[name="select_date_time"][value="' + selectedValue + '"]').prop('checked', true).trigger('change');

                var currentStepDiv = $(".step-content:visible");
                var currentStep = currentStepDiv.data('step');

                if ($("#serviceBookingForm").valid()) {
                    var nextStep = currentStep + 1;
                    if (nextStep <= totalSteps) {
                        updateWizard(nextStep);
                    }
                } else {
                    console.log("Validation failed. Stay on the current step.");
                }
            });

            // Handle Back button click
            $('#backbtn').on('click', function() {
                let selectedValue = $('input[name="select_date_time"]:checked').val();
                $('input[name="select_date_time_2"][value="' + selectedValue + '"]').prop('checked', true).trigger('change');
                var currentStepDiv = $(".step-content:visible");
                var currentStep = currentStepDiv.data('step');
                var prevStep = currentStep - 1;
                if (prevStep >= 1) {
                    updateWizard(prevStep);
                }
            });

            // Handle the wizard step changes
            const steps = $('.step-content'),
                headings = $('.step-heading');
            let currentStep = 1,
                totalSteps = steps.length;

            function updateWizard(step) {
                steps.hide().filter(`[data-step="${step}"]`).show();
                headings.removeClass('editing').filter(`[data-step="${step}"]`).addClass('editing');
                $('#backbtn').toggle(step > 1);
                $('#nextbtn').toggle(step < totalSteps);
                $('#confirmBookingBtn').toggle(step === totalSteps);
            }

            // Initialize the first step
            updateWizard(currentStep);

            // Make sure only a limited number of servicemen can be selected
            const maxSelected = <?php echo $maxSelected; ?>;
            $('input[name="servicemen-list"]').change(function() {
                const checkedCount = $('input[name="servicemen-list"]:checked').length;
                if (checkedCount > maxSelected) {
                    $(this).prop('checked', false);
                    alert(`You can only select up to ${maxSelected} servicemen.`);
                }
            });

            // Synchronize input values between related fields
            function syncInput(sourceSelector, targetSelector) {
                $(sourceSelector).on('change', function() {
                    var value = $(this).val();
                    $(targetSelector).val(value);
                    updateDateTimeInput();
                });
            }

            syncInput('.datetime-local', '.datetime-local-1');
            syncInput('.time-local', '.time-local-1');
            syncInput('.datetime-local-1', '.datetime-local');
            syncInput('.time-local-1', '.time-local');

            // Update the date and time input
            function updateDateTimeInput() {
                var date = $('.datetime-local-1').val();
                var time = $('.time-local-1').val();
                if (date && time) {
                    $('#dateTimeInput').val(date + ' ' + time);
                }
            }
        });
    </script>
@endpush




