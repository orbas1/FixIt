@use('App\Models\Booking')
@use('App\Models\CommissionHistory')
@use('app\Helpers\Helpers')
@use('App\Enums\BookingEnum')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\SymbolPositionEnum')
@extends('backend.layouts.master')
@section('title', __('static.dashboard.dashboard'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@php
    $dateRange = Helpers::getStartAndEndDate(request('sort'), request('start'), request('end'));
    $start_date = $dateRange['start'] ?? null;
    $end_date = $dateRange['end'] ?? null;
@endphp

@section('content')
    @hasanyrole(['admin', 'provider', 'serviceman'])
        <div class="main-dashboard-box">
        <div class="row g-sm-4 g-3">
            <div class="col-12 text-end">
                <form action="" method="GET" id="sort-form" class="dashboard-short-form">
                    <div class="short-box">
                        <label class="form-label">Sort by</label>
                        <select class="form-select" id="sort" name="sort">
                            <option class="select-placeholder" value="today"
                                {{ request('sort') == 'today' ? 'selected' : '' }}>
                                {{ __('static.today') }}
                            </option>
                            <option class="select-placeholder" value="this_week"
                                {{ request('sort') == 'this_week' ? 'selected' : '' }}>
                                {{ __('static.this_week') }}
                            </option>
                            <option class="select-placeholder" value="this_month"
                                {{ request('sort') == 'this_month' ? 'selected' : '' }}>
                                {{ __('static.this_month') }}
                            </option>
                            <option class="select-placeholder" value="this_year"
                                {{ request('sort') == 'this_year' || !request('sort') ? 'selected' : '' }}>
                                {{ __('static.this_year') }}
                            </option>
                            <option class="select-placeholder" value="custom"
                                {{ request('sort') == 'custom' ? 'selected' : '' }}>
                                {{ __('static.custom_range') }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group custom-date-range d-none" id="custom-date-range">
                        <label for="start_end_date">{{ __('static.report.select_date') }}</label>
                        <input type="text" class="form-control filter-dropdown" id="start_end_date" name="start_end_date"
                            placeholder="{{ __('static.service_package.select_date') }}">
                    </div>
                </form>
            </div>
            <div class="col-12">
                <div class="row g-sm-4 g-3">
                    @role('admin')
                        <div class="col-xxl-4 col-md-6">
                            <div class="welcome-box">
                                <div class="top-box">
                                    <img src="{{ asset('admin/images/welcome-shape.svg') }}" class="shape" alt="">
                                </div>
                                <div class="user-image">
                                    <img src="{{ asset('admin/images/avatar/gradient-circle.svg') }}" class="circle"
                                        alt="">
                                    @if (Auth::user()->getFirstMediaUrl('image'))
                                        <img class="img-fluid" src="{{ Auth::user()->getFirstMediaUrl('image') }}"
                                            alt="header-user">
                                    @else
                                        <div class="initial-letter">{{ substr(Auth::user()->name, 0, 1) }}</div>
                                    @endif
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-check check-icon">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>

                                <div class="user-details">
                                    <h3>{{ __('static.hello') }}, {{ Auth::user()->name }}.</h3>
                                    <p>{{ __('static.welcome_to_admin_clan') }}</p>
                                </div>
                            </div>
                        </div>
                    @endrole
                    @unlessrole(['serviceman'])
                        <div class="col-xxl-2 col-md-3 col-sm-6">
                            <a href="{{ route('backend.serviceman.index') }}">
                                <div class="total-box color-1">
                                    <div class="top-box">
                                        <svg>
                                            <use xlink:href="{{ asset('admin/images/svg/total-service.svg#servicemen') }}">
                                            </use>
                                        </svg>
                                        <div data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            data-bs-custom-class="custom-tooltip"
                                            data-bs-title="{{ __('static.dashboard.total_servicemen') }}">
                                            <h4>{{ Helpers::getTotalServicemen($start_date, $end_date) }}</h4>
                                            <h6>{{ __('static.dashboard.total_servicemen') }}</h6>
                                        </div>
                                    </div>

                                    <div id="servicemen-chart"></div>

                                    @if (Helpers::getTotalServicemenPercentage($start_date, $end_date)['status'] == 'decrease')
                                        <div class="bottom-box down">
                                            <svg>
                                                <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}">
                                                </use>
                                            </svg>
                                            <span>
                                            @else
                                                <div class="bottom-box up">
                                                    <svg>
                                                        <use
                                                            xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}">
                                                        </use>
                                                    </svg>
                                                    <span>
                                    @endif
                                    {{ Helpers::getTotalServicemenPercentage($start_date, $end_date)['percentage'] }}% </span>
                                </div>
                        </div>
                        </a>
                    </div>
                @endunlessrole

                @unlessrole(['provider', 'serviceman'])
                    <div class="col-xxl-2 col-md-3 col-sm-6">
                        <a href="{{ route('backend.provider.index') }}">
                            <div class="total-box color-5">
                                <div class="top-box">
                                    <svg>
                                        <use xlink:href="{{ asset('admin/images/svg/total-service.svg#verified-provider') }}">
                                        </use>
                                    </svg>
                                    <div data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        data-bs-custom-class="custom-tooltip" data-bs-title="Providers">
                                        <h4>{{ Helpers::getTotalProviders($start_date, $end_date) }}</h4>
                                        <h6>{{ __('static.dashboard.providers') }}</h6>
                                    </div>
                                </div>

                                <div id="verified-chart"></div>

                                @if (Helpers::getTotalProvidersPercentage($start_date, $end_date)['status'] == 'decrease')
                                    <div class="bottom-box down">
                                        <svg>
                                            <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                        </svg>
                                        <span>
                                        @else
                                            <div class="bottom-box up">
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}">
                                                    </use>
                                                </svg>
                                                <span>
                                @endif
                                {{ Helpers::getTotalProvidersPercentage($start_date, $end_date)['percentage'] }}%</span>
                            </div>
                    </div>
                    </a>
                </div>
            @endunlessrole

            @unlessrole(['serviceman'])
                <div class="col-xxl-2 col-md-3 col-sm-6">
                    <a href="{{ route('backend.withdraw-request.index') }}">
                        <div class="total-box color-3">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#providers') }}">
                                    </use>
                                </svg>
                                <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Provider Withdraw">
                                    <h4>{{ Helpers::getProviderWithdraw($start_date, $end_date) }}</h4>
                                    <h6>{{ __('static.dashboard.provider_withdraw') }}</h6>
                                </div>
                            </div>

                            <div id="provider-chart"></div>

                            @if (Helpers::getProviderWithdrawPercentage($start_date, $end_date)['status'] == 'decrease')
                                <div class="bottom-box down">
                                    <svg>
                                        <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                    </svg>
                                    <span>
                                    @else
                                        <div class="bottom-box up">
                                            <svg>
                                                <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}">
                                                </use>
                                            </svg>
                                            <span>
                            @endif
                            {{ Helpers::getProviderWithdrawPercentage($start_date, $end_date)['percentage'] }} %</span>
                        </div>
                </div>
                </a>
            </div>
        @endunlessrole
        @unlessrole(['provider'])
            <div class="col-xxl-2 col-md-3 col-sm-6">
                <a href="{{ route('backend.serviceman-withdraw-request.index') }}">
                    <div class="total-box color-2">
                        <div class="top-box">
                            <svg>
                                <use xlink:href="{{ asset('admin/images/svg/total-service.svg#service-withdrow') }}">
                                </use>
                            </svg>
                            <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                                data-bs-title="Serviceman Withdraw">
                                <h4>{{ Helpers::getServicemanWithdraw($start_date, $end_date) }}</h4>
                                <h6>{{ __('static.dashboard.serviceman_withdraw') }}</h6>
                            </div>
                        </div>

                        <div class="progress-box">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 75%">
                                </div>
                            </div>
                        </div>

                        @if (Helpers::getServicemanWithdrawPercentage($start_date, $end_date)['status'] == 'decrease')
                            <div class="bottom-box down">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                </svg>
                                <span>
                                @else
                                    <div class="bottom-box up">
                                        <svg>
                                            <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}">
                                            </use>
                                        </svg>
                                        <span>
                        @endif
                        {{ Helpers::getServicemanWithdrawPercentage($start_date, $end_date)['percentage'] }}%</span>
                    </div>
            </div>
            </a>
        </div>
    @endunlessrole

    @unlessrole(['serviceman'])
        <div class="col-xxl-2 col-md-3 col-sm-6">
            <a href="{{ route('backend.service.index') }}">
                <div class="total-box color-3">
                    <div class="top-box">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/total-service.svg#service') }}">
                            </use>
                        </svg>
                        <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                            data-bs-title="Services">
                            <h4>{{ Helpers::getServicesCount($start_date, $end_date) }}</h4>
                            <h6>{{ __('static.dashboard.services') }}</h6>
                        </div>
                    </div>

                    <div id="service-chart"></div>

                    @if (Helpers::getTotalServicesPercentage($start_date, $end_date)['status'] == 'decrease')
                        <div class="bottom-box down">
                            <svg>
                                <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                            </svg>
                            <span>
                            @else
                                <div class="bottom-box up">
                                    <svg>
                                        <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                    </svg>
                                    <span>
                    @endif
                    {{ Helpers::getTotalServicesPercentage($start_date, $end_date)['percentage'] }}%</span>
                </div>
        </div>
        </a>
        </div>
    @endunlessrole

    @unlessrole(['provider'])
        <div class="col-xxl-2 col-md-3 col-sm-6">
            <a href="{{ route('backend.review.index') }}">

                <div class="total-box color-4">
                    <div class="top-box">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/total-service.svg#reviews') }}">
                            </use>
                        </svg>
                        <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                            data-bs-title="Reviews">
                            <h4>{{ Helpers::getReviewsCount($start_date, $end_date) }}</h4>
                            <h6>{{ __('static.dashboard.reviews') }}</h6>
                        </div>
                    </div>

                    <div id="review-chart"></div>

                    @if (Helpers::getTotalReviewsPercentage($start_date, $end_date)['status'] == 'decrease')
                        <div class="bottom-box down">
                            <svg>
                                <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                            </svg>
                            <span>
                            @else
                                <div class="bottom-box up">
                                    <svg>
                                        <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                    </svg>
                                    <span>
                    @endif
                    {{ Helpers::getTotalReviewsPercentage($start_date, $end_date)['percentage'] }}%</span>
                </div>
        </div>
        </a>
        </div>
    @endunlessrole

    @unlessrole(['provider', 'admin'])
        <div class="col-xxl-2 col-md-3 col-sm-6">

            <div class="total-box color-4">
                <div class="top-box">
                    <svg>
                        <use xlink:href="{{ asset('admin/images/svg/total-service.svg#reviews') }}"></use>
                    </svg>
                    <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Wallet">
                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                            <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ isset(auth()->user()->servicemanWallet) ? auth()->user()->servicemanWallet->balance : 0.0 }}</h4>
                        @else
                            <h4>{{ isset(auth()->user()->servicemanWallet) ? auth()->user()->servicemanWallet->balance : 0.0 }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                        @endif
                        <h6>{{ __('static.dashboard.Wallet') }}</h6>
                    </div>
                </div>

                <div id="review-chart"></div>

                @if (Helpers::getTotalReviewsPercentage($start_date, $end_date)['status'] == 'decrease')
                    <div class="bottom-box down">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                        </svg>
                        <span>
                        @else
                            <div class="bottom-box up">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                </svg>
                                <span>
                @endif
                {{ Helpers::getTotalReviewsPercentage($start_date, $end_date)['percentage'] }}%</span>
            </div>
        </div>
        </div>
    @endunlessrole

    <div class="col-xxl-2 col-md-3 col-sm-6">
        <a href="{{ route('backend.commission.index') }}">

            <div class="total-box color-5">
                <div class="top-box">
                    <svg>
                        <use xlink:href="{{ asset('admin/images/svg/total-service.svg#online-payment') }}">
                        </use>
                    </svg>
                    <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Online Payment {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getTotalPayment($start_date, $end_date) }}" >
                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                            <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getTotalPayment($start_date, $end_date) }}</h4>
                        @else
                            <h4>{{ Helpers::getTotalPayment($start_date, $end_date) }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                        @endif
                        <h6>{{ __('static.dashboard.online_payment') }}</h6>
                    </div>
                </div>

                <div id="onlinePayment-chart"></div>

                @if (Helpers::getTotalPaymentPercentage($start_date, $end_date)['status'] == 'decrease')
                    <div class="bottom-box down">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                        </svg>
                        <span>
                        @else
                            <div class="bottom-box up">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                </svg>
                                <span>
                @endif
                {{ Helpers::getTotalPaymentPercentage($start_date, $end_date)['percentage'] }}%</span>
            </div>
    </div>
    </a>
    </div>
    <div class="col-xxl-2 col-md-3 col-sm-6">
        <a href="{{ route('backend.commission.index') }}">

            <div class="total-box color-6">
                <div class="top-box">
                    <svg>
                        <use xlink:href="{{ asset('admin/images/svg/total-service.svg#offline-payment') }}">
                        </use>
                    </svg>
                    <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="Offline Payment {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getTotalPayment($start_date, $end_date, 'cash') }}">
                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                            <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getTotalPayment($start_date, $end_date, 'cash') }}</h4>
                        @else
                            <h4>{{ Helpers::getTotalPayment($start_date, $end_date, 'cash') }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                        @endif
                        <h6>{{ __('static.dashboard.offline_payment') }}</h6>
                    </div>
                </div>

                <div id="offline-payment-chart"></div>

                @if (Helpers::getTotalPaymentPercentage($start_date, $end_date, 'cash')['status'] == 'decrease')
                    <div class="bottom-box down">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                        </svg>
                        <span>
                        @else
                            <div class="bottom-box up">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                </svg>
                                <span>
                @endif
                {{ Helpers::getTotalPaymentPercentage($start_date, $end_date, 'cash')['percentage'] }}%</span>
            </div>
    </div>
    </a>
    </div>

    <div class="col-xxl-2 col-md-3 col-sm-6">
        <a href="{{ route('backend.booking.index') }}">

            <div class="total-box color-7">
                <div class="top-box">
                    <svg>
                        <use xlink:href="{{ asset('admin/images/svg/total-service.svg#booking') }}">
                        </use>
                    </svg>
                    <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                        data-bs-title="Booking">
                        <h4>{{ Helpers::getTotalBookings($start_date, $end_date) }}</h4>
                        <h6>{{ __('static.dashboard.booking') }}</h6>
                    </div>
                </div>

                <div class="progress-box">
                    <div class="progress progress-info">
                        <div class="progress-bar" style="width: 75%">
                        </div>
                    </div>
                </div>

                @if (Helpers::getTotalBookingPercentage($start_date, $end_date)['status'] == 'decrease')
                    <div class="bottom-box down">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                        </svg>
                        <span>
                        @else
                            <div class="bottom-box up">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                </svg>
                                <span>
                @endif
                {{ Helpers::getTotalBookingPercentage($start_date, $end_date)['percentage'] }}%</span>
            </div>
    </div>
    </a>
    </div>
    @unlessrole(['provider', 'serviceman'])
        <div class="col-xxl-2 col-md-3 col-sm-6">
            <a href="{{ route('backend.customer.index') }}">

                <div class="total-box color-1">
                    <div class="top-box">
                        <svg>
                            <use xlink:href="{{ asset('admin/images/svg/total-service.svg#customers') }}">
                            </use>
                        </svg>
                        <div data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                            data-bs-title="Customers">
                            <h4>{{ Helpers::getTotalCustomers($start_date, $end_date) }}</h4>
                            <h6>{{ __('static.dashboard.customers') }}</h6>
                        </div>
                    </div>

                    <div id="customers-chart"></div>

                    @if (Helpers::getTotalCustomersPercentage($start_date, $end_date)['status'] == 'decrease')
                        <div class="bottom-box down">
                            <svg>
                                <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                            </svg>
                            <span>
                            @else
                                <div class="bottom-box up">
                                    <svg>
                                        <use xlink:href="{{ asset('admin/images/svg/down-arrow-1.svg#downArrow') }}"></use>
                                    </svg>
                                    <span>
                    @endif
                    {{ Helpers::getTotalCustomersPercentage($start_date, $end_date)['percentage'] }}%</span>
                </div>
        </div>
        </a>
        </div>
    @endunlessrole

    </div>
    </div>

    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-body">
                <div
                    class="dashboard-box-list-2 row row-cols-xxl-6 row-cols-xl-4 row-cols-md-3 row-cols-sm-2 row-cols-1 gx-0 gy-lg-4 gy-3">
                    <div class="col">
                        <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::PENDING]) }}"
                            class="dashboard-box box-color-1">
                            <div class="svg-icon">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/box-2.svg#box-2') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h5>{{ __('static.booking.pending') }}</h5>
                                <h3>{{ Booking::countByStatus($bookings, BookingEnum::PENDING, $start_date, $end_date) }}
                                </h3>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ON_GOING]) }}"
                            class="dashboard-box box-color-2">
                            <div class="svg-icon">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/box-2.svg#box-2') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h5>{{ __('static.booking.on_going') }}</h5>
                                <h3>{{ Booking::countByStatus($bookings, BookingEnum::ON_GOING, $start_date, $end_date) }}
                                </h3>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ON_THE_WAY]) }}"
                            class="dashboard-box box-color-3">
                            <div class="svg-icon">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/box-2.svg#box-2') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h5>{{ __('static.booking.on_the_way') }}</h5>
                                <h3>{{ Booking::countByStatus($bookings, BookingEnum::ON_THE_WAY, $start_date, $end_date) }}
                                </h3>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::COMPLETED]) }}"
                            class="dashboard-box box-color-4">
                            <div class="svg-icon">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/box-2.svg#box-2') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h5>{{ __('static.booking.completed') }}</h5>
                                <h3>{{ Booking::countByStatus($bookings, BookingEnum::COMPLETED, $start_date, $end_date) }}
                                </h3>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::CANCEL]) }}"
                            class="dashboard-box box-color-5">
                            <div class="svg-icon">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/box-2.svg#box-2') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h5>{{ __('static.booking.cancel') }}</h5>
                                <h3>{{ Booking::countByStatus($bookings, BookingEnum::CANCEL, $start_date, $end_date) }}
                                </h3>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ON_HOLD]) }}"
                            class="dashboard-box box-color-6">
                            <div class="svg-icon">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/box-2.svg#box-2') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h5>{{ __('static.booking.on_hold') }}</h5>
                                <h3>{{ Booking::countByStatus($bookings, BookingEnum::ON_HOLD, $start_date, $end_date) }}
                                </h3>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="row g-sm-4 g-3">
            @unlessrole(['serviceman'])
                <div class="col-sm-4">
                    <div class="dashboard-card">
                        <div class="card-title">
                            <h4>{{ __('static.dashboard.service_types') }}</h4>
                        </div>
                        <div class="card-body">
                            <div id="service-pie-chart" class="service-pie-chart"></div>
                        </div>
                    </div>
                </div>
            @endunlessrole

            @unlessrole(['serviceman'])
                <div class="col-sm-8">
                    <div class="dashboard-card">
                        <div class="card-title">
                            <h4>{{ __('static.dashboard.top_services') }}</h4>
                            <a href="{{ route('backend.service.index') }}">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table top-service-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('static.dashboard.name') }}</th>
                                            <th>{{ __('static.dashboard.provider') }}</th>
                                            <th>{{ __('static.dashboard.bookings') }}</th>
                                            <th>{{ __('static.dashboard.type') }}</th>
                                            <th>{{ __('static.dashboard.ratings') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($services->paginate(4) as $service)
                                            <tr>
                                                <td>
                                                    <div class="service-details-box">
                                                        <img src="{{ $service?->media?->first()?->getUrl() ?? asset('admin/images/service/1.png') }}"
                                                            class="img-fluid service-image" alt="">
                                                        <div class="service-details">
                                                            <h5>{{ $service->title }}</h5>
                                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                <h6>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($service->price, 2) }}
                                                            @else
                                                                <h6>{{ number_format($service->price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}    
                                                            @endif
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="service-details-box">
                                                        @php
                                                            $media = $service?->user?->getFirstMedia('image');
                                                            $imageUrl = $media ? $media->getUrl() : null;
                                                        @endphp

                                                        @if ($imageUrl)
                                                            <img src="{{ $imageUrl }}" alt="Image"
                                                                class="img-fluid service-image rounded-circle">
                                                        @else
                                                            <div class="initial-letter">
                                                                {{ strtoupper(substr($service?->user?->name, 0, 1)) }}</div>
                                                        @endif
                                                        <div class="service-details">
                                                            <h5>{{ $service->user?->name }}</h5>
                                                            <h6>{{ $service->user?->email }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $service->bookings_count }}</td>
                                                <td>{{ $service->type }}</td>
                                                <td>
                                                    <div class="rating">
                                                        <img src="{{ asset('admin/images/svg/star.svg') }}"
                                                            class="img-fluid" alt="">
                                                        <h6>{{ number_format($service->rating_count, 1) }}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <div class="no-table-data">
                                                {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                    alt=""> --}}
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('admin/images/no-table-data-2.svg#no-data') }}">
                                                    </use>
                                                </svg>
                                                <p>{{ __('static.dashboard.data_not_found') }}</p>
                                            </div>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endunlessrole
            @role('admin')
                <div class="col-sm-5">
                    <div class="dashboard-card">
                        <div class="card-title">
                            <h4>{{ __('static.dashboard.top_providers') }}</h4>
                            <a href="{{ route('backend.provider.index') }}">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table top-providers-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('static.dashboard.name') }}</th>
                                            <th>{{ __('static.dashboard.type') }}</th>
                                            <th>{{ __('static.dashboard.bookings') }}</th>
                                            <th>{{ __('static.dashboard.experience') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($Providers as $provider)
                                            <tr>
                                                <td>
                                                    <div class="service-details-box">
                                                        @php
                                                            $media = $provider?->getFirstMedia('image');
                                                            $imageUrl = $media ? $media->getUrl() : null;
                                                        @endphp

                                                        @if ($imageUrl)
                                                            <img src="{{ $imageUrl }}" alt="Image"
                                                                class="img-fluid service-image rounded-circle">
                                                        @else
                                                            <div class="initial-letter">
                                                                {{ strtoupper(substr($provider?->name, 0, 1)) }}</div>
                                                        @endif

                                                        <div class="service-details">
                                                            <h5>{{ $provider?->name }}</h5>
                                                            <h6>{{ $provider?->email }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $provider?->type }}</td>

                                                <td>{{ $provider?->bookings->count() }}</td>
                                                <td>{{ $provider?->experience_duration }}+
                                                    {{ $provider?->experience_interval }}
                                                </td>

                                            </tr>
                                        @empty
                                            <div class="no-table-data">
                                                {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                    alt=""> --}}
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('admin/images/no-table-data-2.svg#no-data') }}">
                                                    </use>
                                                </svg>
                                                <p>{{ __('static.dashboard.data_not_found') }}</p>
                                            </div>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endrole

            @role('provider')
                <div class="col-sm-5">
                    <div class="dashboard-card">
                        <div class="card-title">
                            <h4>{{ __('static.dashboard.top_servicemen') }}</h4>
                            <a href="{{ route('backend.serviceman.index') }}">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table top-providers-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('static.dashboard.name') }}</th>
                                            <th>{{ __('static.dashboard.ratings') }}</th>
                                            <th>{{ __('static.dashboard.experience') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topServicemen as $Serviceman)
                                            <tr>
                                                <td>
                                                    <div class="service-details-box">
                                                        @php
                                                            $media = $Serviceman?->getFirstMedia('image');
                                                            $imageUrl = $media ? $media->getUrl() : null;
                                                        @endphp

                                                        @if ($imageUrl)
                                                            <img src="{{ $imageUrl }}" alt="Image"
                                                                class="img-fluid service-image rounded-circle">
                                                        @else
                                                            <div class="initial-letter">
                                                                {{ strtoupper(substr($Serviceman?->name, 0, 1)) }}</div>
                                                        @endif

                                                        <div class="service-details">
                                                            <h5>{{ $Serviceman?->name }}</h5>
                                                            <h6>{{ $Serviceman?->email }}</h6>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    @isset($Serviceman->ServicemanReviewRatings)
                                                        <div class="rate">
                                                            @for ($i = 0; $i < Helpers::getServicemanReviewRatings($Serviceman); ++$i)
                                                                <img src="{{ asset('admin/images/svg/star.svg') }}"
                                                                    alt="star" class="img-fluid star">
                                                            @endfor
                                                            <small>({{ $Serviceman->ServicemanReviewRatings }})</small>
                                                        </div>
                                                    @endisset
                                                </td>
                                                <td>{{ $Serviceman?->experience_duration }}+
                                                    {{ $Serviceman?->experience_interval }} </td>
                                            </tr>
                                        @empty
                                            <div class="no-table-data">
                                                {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                    alt=""> --}}
                                                <svg>
                                                    <use
                                                        xlink:href="{{ asset('admin/images/no-table-data-2.svg#no-data') }}">
                                                    </use>
                                                </svg>
                                                <p>{{ __('static.dashboard.data_not_found') }}</p>
                                            </div>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endrole

            <div class="col-sm-7">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.dashboard.revenue') }}</h4>
                        <ul class="chart-list">
                            @role('provider')
                                <li>
                                    <span class="color-1"></span>
                                    {{ __('static.dashboard.provider') }}
                                </li>
                            @endrole
                            @role('admin')
                                <li>
                                    <span class="color-2"></span>
                                    {{ __('static.dashboard.admin') }}
                                </li>
                            @endrole
                            @role('serviceman')
                                <li>
                                    <span class="color-3"></span>
                                    {{ __('static.dashboard.serviceman') }}
                                </li>
                            @endrole
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div id="revenue-chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.dashboard.recent_booking') }}</h4>
                        <a href="{{ route('backend.booking.index') }}">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table recent-booking-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('static.dashboard.booking_id') }}</th>
                                        <th>{{ __('static.dashboard.service') }}</th>
                                        <th>{{ __('static.dashboard.provider') }}</th>
                                        <th>{{ __('static.dashboard.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bookings->take(4) as $booking)
                                        <tr>
                                            <td>
                                                <span class="badge badge-booking">#{{ $booking?->booking_number }}</span>
                                            </td>

                                            <td>
                                                <div class="service-details-box">
                                                    @php
                                                        $media = $booking?->service?->getFirstMedia('image');
                                                        $imageUrl = $media ? $media->getUrl() : null;
                                                    @endphp

                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="Image"
                                                            class="img-fluid service-image">
                                                    @else
                                                        <div class="initial-letter">
                                                            {{ strtoupper(substr($booking?->service?->title, 0, 1)) }}
                                                        </div>
                                                    @endif

                                                    <div class="service-details">

                                                        <h5>{{ $booking?->service?->title }}</h5>
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            <h6>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($booking?->service?->price, 2) }}</h6>
                                                        @else
                                                            <h6>{{ number_format($booking?->service?->price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</h6>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="service-details-box">
                                                    @php
                                                        $media = $booking?->provider?->getFirstMedia('image');
                                                        $imageUrl = $media ? $media->getUrl() : null;
                                                    @endphp

                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="Image"
                                                            class="img-fluid service-image rounded-circle">
                                                    @else
                                                        <div class="initial-letter">
                                                            {{ strtoupper(substr($booking?->provider?->name, 0, 1)) }}
                                                        </div>
                                                    @endif

                                                    <div class="service-details">
                                                        <h5>{{ $booking?->provider?->name }}</h5>
                                                        <h6>{{ $booking?->provider?->email }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if (count($booking->sub_bookings))
                                                    <span
                                                        class="badge booking-status-{{ $booking->sub_bookings?->first()?->booking_status?->color_code }}">{{ $booking->sub_bookings?->first()?->booking_status?->name }}</span>
                                                @elseif (isset($booking->booking_status?->color_code))
                                                    <span
                                                        class="badge booking-status-{{ $booking->booking_status?->color_code }}">{{ $booking->booking_status?->name }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <div class="no-table-data">
                                            {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt=""> --}}
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset('admin/images/no-table-data-2.svg#no-data') }}">
                                                </use>
                                            </svg>
                                            <p>{{ __('static.dashboard.data_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.dashboard.latest_reviews') }}</h4>
                        <a href="{{ route('backend.review.index') }}">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table recent-withdraw-requests-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('static.dashboard.service') }}</th>
                                        <th class="text-start">{{ __('static.dashboard.consumer') }}</th>
                                        <th>{{ __('static.dashboard.ratings') }}</th>
                                        <th>{{ __('static.dashboard.created_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reviews as $review)
                                        <tr>
                                            <td>
                                                <div class="service-details-box">
                                                    @php
                                                        $media = $review?->service?->getFirstMedia('image');
                                                        $imageUrl = $media ? $media->getUrl() : null;
                                                    @endphp

                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="Image"
                                                            class="img-fluid service-image">
                                                    @else
                                                        <div class="initial-letter">
                                                            {{ strtoupper(substr($review?->service?->title, 0, 1)) }}
                                                        </div>
                                                    @endif

                                                    <div class="service-details">
                                                        <h5>{{ $review?->service?->title }}</h5>
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            <h6>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($review?->service?->price, 2) }}</h6>
                                                        @else
                                                            <h6>{{ number_format($review?->service?->price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</h6>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="service-details-box">
                                                    @php
                                                        $media = $review?->consumer?->getFirstMedia('image');
                                                        $imageUrl = $media ? $media->getUrl() : null;
                                                    @endphp

                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="Image"
                                                            class="img-fluid service-image rounded-circle">
                                                    @else
                                                        <div class="initial-letter">
                                                            {{ strtoupper(substr($review?->consumer?->name, 0, 1)) }}
                                                        </div>
                                                    @endif

                                                    <div class="service-details">
                                                        <h5>{{ $review?->consumer?->name }}</h5>
                                                        <h6>{{ $review?->consumer?->email }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="rate justify-content-center gap-1">
                                                    @for ($i = 0; $i < $review?->rating; ++$i)
                                                        <img src="{{ asset('admin/images/svg/star.svg') }}"
                                                            alt="star" class="img-fluid star">
                                                    @endfor
                                                    <small>({{ $review?->rating }})</small>
                                                </div>
                                            </td>
                                            <td>
                                                {{ date('d-M-Y', strtotime($review->created_at)) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <div class="no-table-data">
                                            {{-- <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt=""> --}}
                                            <svg>
                                                <use
                                                    xlink:href="{{ asset('admin/images/no-table-data-2.svg#no-data') }}">
                                                </use>
                                            </svg>
                                            <p>{{ __('static.dashboard.data_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    @else
    
    @endhasanyrole
@endsection
@push('js')
    <script src="{{ asset('admin/js/apex-chart.js') }}"></script>
    <script src="{{ asset('admin/js/custom-apexchart.js') }}"></script>
    <script src="{{ asset('admin/js/flatpickr.js') }}"></script>
    <script src="{{ asset('admin/js/custom-flatpickr.js') }}"></script>

    <script>
        $(document).ready(function() {
            const filterVal = $('#sort').val();
            if (filterVal === 'custom') {
                $('#custom-date-range').removeClass('d-none');
            } else {
                $('#custom-date-range').addClass('d-none');
            }

            function formatDate(date) {
                const parts = date.split('/');
                if (parts.length === 3) {
                    return `${parts[0]}-${parts[1]}-${parts[2]}`;
                }
                return date;
            }

            $('#start_end_date').on('change', function() {

                const selectedDateRange = $(this).val();

                if (selectedDateRange) {
                    const dateRange = selectedDateRange.split(' to ');

                    if (dateRange.length === 2) {
                        const startDate = formatDate(dateRange[0]);
                        const endDate = formatDate(dateRange[1]);
                        const urlParams = new URLSearchParams(window.location.search);
                        urlParams.set('sort', 'custom');
                        urlParams.set('start', startDate);
                        urlParams.set('end', endDate);


                        window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
                    }
                }
            });

            $('#sort').on('change', function() {

                const selectedSort = $(this).val();

                if (selectedSort === 'custom') {
                    $('#custom-date-range').removeClass('d-none');
                } else {
                    window.history.replaceState(null, null, location.pathname);
                    $('#custom-date-range').addClass('d-none');
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('sort', selectedSort);
                    window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
                }
            });
            var serviceTypeCounts = <?php echo json_encode(Helpers::getServiceTypeCount($start_date, $end_date)['series']); ?>;
            var TotalServiceCount = <?php echo Helpers::getServicesCount($start_date, $end_date); ?>


            var servicePieChart = {
                series: serviceTypeCounts,
                labels: ["User Site", "Remotely", "Provider Site"],
                chart: {
                    height: 338,
                    type: "donut",
                },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        donut: {
                            size: "75%",
                            background: '#F4F6F2',

                            labels: {
                                show: true,
                                name: {
                                    offsetY: -1,
                                },
                                value: {
                                    fontSize: "14px",
                                    offsetY: 10,
                                    fontFamily: "var(--font-family)",
                                    fontWeight: 400,
                                    color: "#52526C",
                                },
                                total: {
                                    show: true,
                                    fontSize: "20px",
                                    offsetY: -1,
                                    fontWeight: 500,
                                    fontFamily: "var(--font-family)",
                                    label: TotalServiceCount,
                                    formatter: () => "Total",
                                },
                            },
                        },
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                colors: ["#27AF4D", "#FFBC58", "#5465FF"],
                fill: {
                    type: "solid",
                },
                legend: {
                    show: true,
                    position: "bottom",
                    horizontalAlign: "center",
                    fontSize: "18px",
                    fontFamily: "var(--font-family)",
                    fontWeight: 500,
                    labels: {
                        colors: "#071B36",
                    },
                    markers: {
                        width: 15,
                        height: 15,
                    },
                },
                stroke: {
                    width: 5,
                },
                responsive: [{
                    breakpoint: 576,
                    options: {
                        chart: {
                            height: 280,
                        },
                        legend: {
                            fontSize: "15px",
                            markers: {
                                width: 12,
                                height: 12,
                            }
                        },
                    },
                }, ],
            };
            var servicePieChart = new ApexCharts(document.querySelector("#service-pie-chart"), servicePieChart);
            servicePieChart.render();


            var revenueData = <?php echo json_encode($data['revenues']); ?>

            var revenueChart = {
                series: [{
                    name: "Revenue",
                    data: revenueData,
                }, ],
                chart: {
                    type: "bar",
                    height: 340,
                    toolbar: {
                        show: false,
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "25%",
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ["transparent"],
                },
                colors: ["var(--primary-color)", "#C9CED4", "#FFBC58"],
                xaxis: {
                    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov",
                        "Dec"
                    ],
                    tickPlacement: "between",
                    labels: {
                        style: {
                            colors: "#888888",
                            fontSize: "14px",
                            fontFamily: "var(--font-family)",
                            fontWeight: 500,
                        },
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    min: 10,
                    max: 80,
                    labels: {
                        style: {
                            colors: "#00162e",
                            fontSize: "14px",
                            fontFamily: "var(--font-family)",
                            fontWeight: 400,
                        },
                    },
                },
                fill: {
                    opacity: 1,
                },
                legend: {
                    show: false,
                },
                grid: {
                    show: true,
                    position: "back",
                    borderColor: "#edeff1",
                },
                responsive: [{
                        breakpoint: 446,
                        options: {
                            xaxis: {
                                type: "category",
                                tickAmount: 5,
                                tickPlacement: "between",
                            },
                        },
                    },
                    {
                        breakpoint: 808,
                        options: {
                            chart: {
                                height: 360,
                            },
                        },
                    },
                ],
            };
            var revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), revenueChart);
            revenueChart.render();
        });
    </script>
@endpush
