@use('App\Models\Booking')
@use('app\Helpers\Helpers')
@use('App\Enums\BookingEnum')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\SymbolPositionEnum')
@extends('backend.layouts.master')

@section('title', __('static.provider_dashboard.general_info'))

@section('content')

    @includeIf('backend.provider-dashboard.index')

    <div class="row g-sm-4 g-3">
        <div class="col-xxl-4 col-xl-5">
            <div class="row g-sm-4 g-3">

                <div class="col-sm-6">
                    <a href="" class="widget-card card">
                        <div>
                            <h3>{{ Helpers::getBookingsCountById($id) }}</h3>
                            <h5>{{ __('static.dashboard.total_bookings') }}</h5>
                        </div>
                        <div class="widget-icon">
                            <i data-feather="calendar"></i>
                        </div>
                    </a>
                </div>
                <div class="col-xxl-6 col-xl-12 col-sm-6 col-12">
                    <a href="" class="widget-card card">
                        <div>
                            <h3>{{ Helpers::getServicemenCountById($id) }}</h3>
                            <h5>{{ __('static.dashboard.total_servicemen') }}</h5>
                        </div>
                        <div class="widget-icon">
                            <i data-feather="user-plus"></i>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="" class="widget-card card">
                        <div>
                            <h3>{{ Helpers::getServicesCountById($id) }}</h3>
                            <h5>{{ __('static.dashboard.total_services') }}</h5>
                        </div>
                        <div class="widget-icon">
                            <i data-feather="settings"></i>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="" class="widget-card card">
                        <div>
                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                <h3>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getBalanceById($id) }}</h3>
                            @else
                                <h3>{{ Helpers::getBalanceById($id) }} {{ Helpers::getDefaultCurrencySymbol() }}</h3>    
                            @endif
                            <h5>{{ __('static.dashboard.wallet_balance') }}</h5>
                        </div>
                        <div class="widget-icon">
                            <i data-feather="credit-card"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xxl-8 col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('static.booking.booking_status') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-sm-4 g-3 booking-status-main">
                        <div class="col-xxl-4 col-sm-6 booking-status-card">
                            <a href=""
                                class="booking-widget-card card">
                                <div>
                                    <h3>{{ Booking::countBookingsByUserIdAndStatus($bookings, $id, BookingEnum::PENDING) }}</h3>
                                    <h5>{{ __('static.booking.pending') }}</h5>
                                </div>
                                <div class="booking-widget-icon">
                                    <i data-feather="box"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-sm-6 booking-status-card">
                            <a href=""
                                class="booking-widget-card card">
                                <div>
                                    <h3>{{ Booking::countByStatus($bookings, BookingEnum::ON_GOING) }}</h3>
                                    <h5>{{ __('static.booking.on_going') }}</h5>
                                </div>
                                <div class="booking-widget-icon">
                                    <i data-feather="calendar"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-sm-6 booking-status-card">
                            <a href=""
                                class="booking-widget-card card">
                                <div>
                                    <h3>{{ Booking::countByStatus($bookings, BookingEnum::ON_THE_WAY) }}</h3>
                                    <h5>{{ __('static.booking.on_the_way') }}</h5>
                                </div>
                                <div class="booking-widget-icon">
                                    <i data-feather="package"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-sm-6 booking-status-card">
                            <a href=""
                                class="booking-widget-card card">
                                <div>
                                    <h3>{{ Booking::countByStatus($bookings, BookingEnum::COMPLETED) }}</h3>
                                    <h5>{{ __('static.booking.completed') }}</h5>
                                </div>
                                <div class="booking-widget-icon">
                                    <i data-feather="truck"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-sm-6 booking-status-card">
                            <a href=""
                                class="booking-widget-card card">
                                <div>
                                    <h3>{{ Booking::countByStatus($bookings, BookingEnum::CANCEL) }}</h3>
                                    <h5>{{ __('static.booking.cancel') }}</h5>
                                </div>
                                <div class="booking-widget-icon">
                                    <i data-feather="x-circle"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-xxl-4 col-sm-6 booking-status-card">
                            <a href=""
                                class="booking-widget-card card">
                                <div>
                                    <h3>{{ Booking::countByStatus($bookings, BookingEnum::ON_HOLD) }}</h3>
                                    <h5>{{ __('static.booking.on_hold') }}</h5>
                                </div>
                                <div class="booking-widget-icon">
                                    <i data-feather="alert-circle"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
