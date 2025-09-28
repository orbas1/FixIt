@extends('backend.layouts.master')
@section('title', __('static.booking.details'))
@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@section('content')
    @use('App\Enums\PaymentStatus')
    @use('App\Enums\BookingEnumSlug')
    @use('App\Enums\RoleEnum')
    @use('app\Helpers\Helpers')
    @use('App\Enums\BookingEnum')
    @use('App\Models\BookingStatus')
    @php
        $statuses = BookingStatus::whereNull('deleted_at')->where('status', true)->get();
        $paymentStatuses = PaymentStatus::ALL;
    @endphp

    @php
        $user = auth()->user();
        $isProvider = $user && !$user->hasRole(RoleEnum::ADMIN) && $user->hasRole(RoleEnum::PROVIDER);
        $isCompleted = $childBooking->booking_status_id == Helpers::getbookingStatusId(BookingEnum::COMPLETED);
    @endphp

    @if ($isProvider && $isCompleted)
        <div class="alert alert-info d-flex" role="alert">
            <div class="alert__icon">
                <i class="ri-info-i"></i>
            </div>
            <div class="alert__content">
                <p>
                     {{ __('static.booking.note') }}
                    
                </p>
            </div>
        </div>
    @endif

    <div class="booking-details-main-box">
        <div class="row g-sm-4 g-3">
            <div class="col-xxl-6 col-xl-4">
                <div class="booking-details-box">
                    <div class="booking-title">
                        <h4>{{ __('static.booking.booking_details') }} #{{ $childBooking->booking_number }}</h4>
                        @if ($childBooking->servicemen->isEmpty() && $childBooking->booking_status_id == Helpers::getbookingStatusId(BookingEnum::ACCEPTED))
                            <button class="assign-btn btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#assignmodal"
                                id="assign_serviceman">{{ __('static.booking.assign') }}</button>
                        @endif
                        @if ($childBooking->booking_status_id == Helpers::getbookingStatusId(BookingEnum::ASSIGNED))
                            <button class="assign-btn btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#assignmodal"
                                id="assign_serviceman">{{ __('static.booking.reassign') }}</button>
                        @endif
                    </div>
                    <div class="booking-content">
                        <ul class="booking-details-list">
                            <li>
                                {{ __('static.service.title') }}:
                                <span>{{ $childBooking?->service?->title }}</span>
                            </li>
                            <li>
                                {{ __('static.service.service_price') }}:
                                <span>{{ $childBooking?->service?->service_rate }}</span>
                            </li>
                            <li>
                                {{ __('static.service.service_type') }}:
                                <span>{{ Helpers::formatServiceType($childBooking->service?->type) }}</span>
                            </li>
                            <li>
                                {{ __('static.booking.payment_method') }}:
                                <span>{{ $childBooking->payment_method }}</span>
                            </li>
                            <li>
                                {{ __('static.booking.payment_status') }}:
                                <span>{{ $childBooking?->payment_status }}</span>
                            </li>
                            <li>
                                {{ __('static.service.required_servicemen') }}:
                                <span>{{ $childBooking?->service?->required_servicemen ?? 'N/A' }}</span>
                            </li>
                            <li>
                                @php
                                    $addonsChargeAmount = Helpers::getTotalAddonCharges($childBooking->id);
                                @endphp
                                {{ __('frontend::static.bookings.add_ons') }}
                                <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ Helpers::covertDefaultExchangeRate($addonsChargeAmount) }}</span>
                            </li>

                            {{-- <li>
                                @php
                                    $extraChargeAmount = Helpers::getTotalExtraCharges(
                                        $booking?->id,
                                    );
                                @endphp
                                <p>{{ __('frontend::static.bookings.extra_charges') }}</p>
                                <span>{{ $defaultSymbol }}{{ Helpers::covertDefaultExchangeRate($extraChargeAmount) }}</span>
                            </li> --}}


                            @php
                                $extraChargeAmount = Helpers::getTotalExtraCharges($childBooking?->id);
                                $extraCharges = $childBooking?->extra_charges ?? collect();
                            @endphp
                            @if ($extraChargeAmount > 0)
                                <li>
                                    @can('backend.booking.edit')
                                        <a href="#extraChargeModal-{{ $childBooking->id }}" data-bs-toggle="modal">{{ __('static.booking.extra_charge') }}</a>
                                        <span>{{ Helpers::covertDefaultExchangeRate($extraChargeAmount) }}</span>
                                    @endcan
                                </li>
                            @endif

                            @if ($childBooking?->total_extra_servicemen > 0)
                                <li>
                                    {{ __('static.service.total_extra_servicemen') }}:
                                    <span>{{ $childBooking->total_extra_servicemen ?? 0 }}</span>
                                </li>
                            @endif
                            @if (isset($childBooking->per_serviceman_charge))
                                <li>
                                    {{ __('static.service.per_serviceman_charge') }}
                                    <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->per_serviceman_charge ?? 0 }}</span>
                                </li>
                            @endif
                            @if (isset($childBooking->total_extra_servicemen_charge))
                                <li>
                                    {{ __('static.service.total_servicemen_charge') }}
                                    <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->total_extra_servicemen_charge ?? 0 }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-xl-4 col-sm-6">
                <div class="booking-details-box">
                    <div class="booking-title">
                        <h4>{{ __('static.consumer_details') }} :</h4>
                    </div>
                    <div class="booking-content">
                        <div class="booking-profile-box">
                            <div class="profile-image-box">
                                @php
                                    $media = $childBooking?->consumer?->getFirstMedia('image');
                                    $imageUrl = $media ? $media->getUrl() : null;
                                @endphp
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" class="img-fluid service-image"
                                        alt="{{ $childBooking?->consumer?->name ?? 'User Image' }}">
                                @else
                                    <div class="initial-letter">
                                        <span>{{ strtoupper($childBooking?->consumer?->name[0]) }}</span>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <h4>{{ $childBooking->consumer->name }}</h4>
                                    <h5>{{ $childBooking->consumer->email }}</h5>
                                </div>
                            </div>
                            <ul class="profile-content-box">
                                <li>{{ __('static.phone') }} : <span>+{{ $childBooking->consumer->code }}
                                        {{ $childBooking->consumer->phone }}</span></li>
                                <li>{{ __('static.country') }} :
                                    <span>{{ $childBooking->consumer?->getPrimaryAddressAttribute()?->country?->name }}</span>
                                </li>
                                <li>{{ __('static.state') }} :
                                    <span>{{ $childBooking->consumer?->getPrimaryAddressAttribute()?->state?->name }}</span>
                                </li>
                                <li>{{ __('static.city') }} :
                                    <span>{{ $childBooking->consumer?->getPrimaryAddressAttribute()?->city }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-xl-4 col-sm-6">
                <div class="booking-details-box">
                    <div class="booking-title">
                        <h4>{{ __('static.provider_details') }} :</h4>
                    </div>
                    <div class="booking-content">
                        <div class="booking-profile-box">
                            <div class="profile-image-box">
                                @php
                                    $media = $childBooking?->provider?->getFirstMedia('image');
                                    $imageUrl = $media ? $media->getUrl() : null;
                                @endphp
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" class="img-fluid"
                                        alt="{{ $childBooking?->provider?->name ?? 'User Image' }}">
                                @else
                                    <div class="initial-letter">
                                        <span>{{ strtoupper($childBooking?->provider?->name[0]) }}</span>
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <a
                                        href="{{ route('backend.provider.general-info', ['id' => $childBooking->provider->id]) }}">
                                        <h4>{{ $childBooking->provider?->name }}</h4>
                                    </a>
                                    <h5 class="name">{{ $childBooking->provider?->email }}</h5>
                                </div>
                            </div>
                            <ul class="profile-content-box">
                                @if (isset($childBooking->provider?->code) && isset($childBooking->provider->phone))
                                    <li>
                                        <span>{{ __('static.phone') }}:</span>
                                        +{{ $childBooking->provider?->code . ' ' . $childBooking->provider->phone }}

                                    </li>
                                @endif
                                @if (isset($childBooking?->provider?->getPrimaryAddressAttribute()?->country?->name))
                                    <li>
                                        <span>{{ __('static.country') }}:</span>
                                        {{ $childBooking->provider->getPrimaryAddressAttribute()->country->name }}

                                    </li>
                                @endif
                                @if (isset($childBooking?->provider?->getPrimaryAddressAttribute()?->state?->name))
                                    <li>
                                        <span>{{ __('static.state') }}:</span>
                                        {{ $childBooking->provider->getPrimaryAddressAttribute()->state->name }}

                                    </li>
                                @endif
                                @if (isset($childBooking?->provider?->getPrimaryAddressAttribute()?->city))
                                    <li>
                                        <span>{{ __('static.city') }}:</span>
                                        {{ $childBooking->provider->getPrimaryAddressAttribute()->city }}

                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @if ($childBooking->servicemen->count() > 0)
                <div class="col-xxl-5">
                    <div class="booking-details-box p-0">
                        <div class="booking-title p-20">
                            <h4>{{ __('static.servicemen_information') }}</h4>
                        </div>
                        <div class="booking-content p-0 mt-0">
                            <div class="table-responsive">
                                <table class="table top-providers-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('static.name') }}</th>
                                            <th>{{ __('static.email') }}</th>
                                            <th>{{ __('static.phone') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($childBooking->servicemen as $serviceman)
                                            <tr>
                                                <td>{{ $serviceman->name }}</td>
                                                <td>{{ $serviceman->email }}</td>
                                                <td>+{{ $serviceman->code . ' ' . $serviceman->phone }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-xxl-4 col-md-6">
                <div class="booking-details-box">
                    <div class="booking-title">
                        <h4>{{ __('static.booking.booking_settings') }}</h4>
                        <button class="btn invoice-btn"><a href="{{ route('invoice', $childBooking->booking_number) }}"
                                class="btn link-btn p-0">{{ __('static.booking.invoice') }}</a></button>
                    </div>
                    <div class="booking-content">
                        <div class="form-group row">
                            <label class="col-12" for="status">{{ __('static.booking.booking_status') }}</label>
                            <div class="col-12">
                                <form
                                    action="{{ route('backend.bookingStatus.update', ['booking_id' => $childBooking->id]) }}"
                                    method="post">
                                    @php
                                        $bookingStatus = $childBooking->booking_status->slug;
                                    @endphp
                                    @csrf
                                    <select class="select-2 form-control" name="booking_status" id="bookingStatusFilter"
                                        onchange="this.form.submit()">
                                        <option value="" class="select-placeholder">
                                            {{ __('static.booking.select_booking_status') }}</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status?->slug }}"
                                                @if ($bookingStatus == $status?->slug) selected @endif>
                                                {{ $status?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12" for="booking-date">{{ __('static.booking.select_date') }}</label>
                            <div class="col-12">
                                <input class="form-control" type="text" id="booking-date"
                                    placeholder="{{ __('static.service_package.select_date') }}">
                                <button id="confirmDateBtn" class="btn btn-primary"
                                    style="display: none;">{{ __('Confirm') }}</button>
                                <form id="updateBookingForm" action="{{ route('backend.booking.updateDateTime') }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $childBooking->id }}">
                                    <input type="hidden" name="date_time" id="selected-date">
                                </form>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-12" for="paymentStatus">{{ __('static.booking.payment_status') }}</label>
                            <div class="col-12">
                                <select class="select-2 form-control" name="payment_status" id="paymentStatus"
                                    data-placeholder="{{ __('static.booking.select_booking_payment_status') }}">
                                    @foreach ($paymentStatuses as $paymentStatus)
                                        <option value="{{ $paymentStatus }}"
                                            @if ($childBooking->payment_status === strtoupper($paymentStatus)) selected @endif>
                                            {{ $paymentStatus }}
                                        </option>
                                    @endforeach
                                </select>
                                <button id="confirmPaymentBtn" class="btn btn-primary" style="display: none;">
                                    {{ __('Confirm') }}</button>
                                <form id="updatePaymentForm" action="{{ route('backend.booking.updatePaymentStatus') }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $childBooking->id }}">
                                    <input type="hidden" name="payment_status" id="payment-status-field">
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xxl-3 col-md-6">
                <div class="booking-details-box p-0">
                    <div class="booking-title p-18 pb-0">
                        <h4>{{ __('static.booking.booking_address') }}</h4>
                    </div>
                    <div class="booking-content">
                        <ul class="booking-address-list">
                            <li>{{ __('static.booking.city') }} : <span>{{ $childBooking?->address?->city }}</span></li>
                            <li>{{ __('static.booking.state') }} : <span>{{ $childBooking?->address?->state?->name }}</span></li>
                            <li>{{ __('static.booking.country') }} : <span>{{ $childBooking?->address?->country?->name }}</span></li>
                            <li>{{ __('static.booking.address') }} : <span>{{ $childBooking?->address?->address }}</span></li>
                            <li>{{ __('static.booking.pin_code') }} : <span>{{ $childBooking?->address?->postal_code }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-5 col-md-6">
                <div class="booking-details-box">
                    <div class="booking-title">
                        <h4>{{ __('static.booking.details') }} #{{ $childBooking->booking_number }}</h4>
                        <h5>{{ __('static.booking.created_at') }}{{ $childBooking->created_at->format('j F Y, g:i A') }}
                        </h5>
                    </div>
                    <div class="booking-content">
                        <ul class="booking-number-list">
                            @forelse ($childBooking->booking_status_logs as $status)
                                <li>
                                    <div class="activity-dot activity-dot-{{ $status->status->hexa_code }}">

                                    </div>
                                    <div class="circle"></div>
                                    <div class="booking-number-box">
                                        <div class="left-box">

                                            <h6 class="date">{{ $status->created_at->format('d-m-Y') }}</h6>
                                            <h6 class="name">{{ $status->status->name }}</h6>
                                            <h6 class="content">{{ $status->description }}</h6>
                                        </div>
                                        <div class="right-box">
                                            <h6>{{ $status->created_at->format('h:i A') }}</h6>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="d-flex">
                                    <div id="activity-dot-not-found" class="activity-dot activity-dot-primary">
                                    </div>
                                    <div class="w-100 ms-3">
                                        <h4 class="no-status">{{ __('static.no_status_log_found') }}</h4>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xxl-7 col-md-6">
                <div class="booking-details-box">
                    <div class="booking-title">
                        <h4>{{ __('static.summary') }}</h4>
                    </div>
                    <div class="booking-content">
                        <ul class="booking-details-list">
                            <li>
                                <span>{{ __('static.booking.payment_method') }}:</span>{{ $childBooking->payment_method }}

                            </li>
                            <li>
                                <span>{{ __('static.booking.payment_status') }}:</span>{{ $childBooking->payment_status }}

                            </li>
                            <li>
                                <span>{{ __('static.booking.coupon_discount') }}:</span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->coupon_total_discount }}

                            </li>
                            <li>
                                <span>{{ __('static.booking.service_discount') }}:</span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->discount ?? 0 }}

                            </li>
                            <li>
                                <span>{{ __('static.booking.service_tax') }}:</span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->tax ?? 0 }}

                            </li>
                            <li>
                                <span>{{ __('static.booking.service_amount') }}:</span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking?->service?->service_rate ?? 0 }}

                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @if ($childBooking->booking_status_id === Helpers::getbookingStatusId(BookingEnum::COMPLETED))
                <div class="col-xxl-6 col-xl-4">
                    <div class="booking-details-box">
                        <div class="booking-title">
                            <h4>{{ __('static.booking.commission_details') }}</h4>
                        </div>
                        <div class="booking-content">
                            <ul class="booking-details-list">
                                @php
                                    $user = auth()->user();
                                    $commissions = $childBooking->commission_history;
                                    $isProvider = $user->role->name === RoleEnum::PROVIDER;
                                    $isAdmin = $user->role->name === RoleEnum::ADMIN;
                                    $isServiceman = $user->role->name === RoleEnum::SERVICEMAN;
                                @endphp

                                @if ($commissions->isNotEmpty())
                                    <li>
                                        {{ __('static.booking.platform_fees') }} :
                                        <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->platform_fees }}</span>
                                    </li>

                                    @foreach ($commissions as $commission)

                                    @if($isAdmin || $isProvider)
                                        <li>
                                            {{ $isAdmin ? __('static.booking.your_commission') : __('static.booking.admin_commission') }}:
                                            <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $commission->admin_commission }}</span>
                                        </li>

                                        <li>
                                            {{ $isProvider ? __('static.booking.your_commission') : __('static.booking.provider_commission') }}:
                                            <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $commission->provider_net_commission }}</span>
                                        </li>
                                    @endif
                                            @if ($commission->serviceman_commissions->isNotEmpty())
                                                @php
                                                    $providerId = $childBooking->provider_id ?? null;
                                                    $servicemanIds = $commission->serviceman_commissions->pluck('serviceman_id')->toArray();
                                                @endphp

                                                @if (!in_array($providerId, $servicemanIds))
                                                    <li class="commission">
                                                    {{ $isServiceman ? __('static.booking.your_commission') : __('static.booking.serviceman_commissions') }}:
                                                    <span>
                                                        {{-- <ul> --}}
                                                        @foreach ($commission->serviceman_commissions as $servicemanCommission)
                                                            @if ($isAdmin || $isProvider || ($isServiceman && $servicemanCommission->serviceman_id === $user->id))
                                                                {{-- <span> --}}
                                                                    <span>{{ $servicemanCommission->serviceman?->name ?? 'N/A' }}:
                                                                        {{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ number_format($servicemanCommission->commission, 2) }}
                                                                    </span>
                                                                {{-- </span> --}}
                                                            @endif
                                                        @endforeach
                                                        {{-- </ul> --}}
                                                    </span>
                                                </li>
                                            @endif
                                        @endif
                                        <li>
                                            {{ __('static.booking.total') }} :
                                            <span>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $childBooking->total }}</span>
                                        </li>
                                    @endforeach
                                @else
                                    <li>
                                        <span class="text-muted">{{ __('No commission data available.') }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal fade" id="assignmodal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content service-man">
                <div class="modal-header">
                    <h5>{{ __('static.assign_service') }}</h5>
                </div>
                <form id="assignServicemanForm" action="{{ route('backend.booking.assignServicemen') }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="service-man-detail">
                            <div class="form-group row">
                                <label class="col-md-2" for="servicemen">{{ __('static.booking.serviceman') }}</label>
                                <div class="col-md-10 error-div select-dropdown">
                                    <select class="select-2 form-control" id="servicemen" search="true"
                                        name="servicemen[]"
                                        data-placeholder="{{ __('static.booking.select_serviceman') }}" multiple>

                                    </select>
                                    @error('servicemen')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <input type="hidden" name="booking_id" value="{{ $childBooking->id }}">
                                    @if ($childBooking->booking_status_id == Helpers::getbookingStatusId(BookingEnum::ASSIGNED))
                                        <input type="hidden" name="reassign" value="1">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="assign-btn btn">{{ __('static.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal extra-charge-modal fade" id="extraChargeModal-{{ $childBooking->id }}">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('static.booking.extra_charge_details') }}</h5>
                    {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @forelse ($extraCharges as $charge)
                        <form class="charge-details-form"
                            action="{{ route('backend.booking.extra-charge.delete', ['booking' => $childBooking->id, 'charge' => $charge->id]) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this extra charge?');">
                            @csrf
                            @method('DELETE')

                            <div class="charge-details-box">
                                <ul class="charge-list">
                                    <li>{{ __('Title') }}: <span>{{ $charge->title }}</span></li>
                                    <li>{{ __('Per Service Amount') }}: <span>{{ $charge->per_service_amount }}</span>
                                    </li>
                                    <li>{{ __('No of service done') }}:
                                        <span>{{ ucfirst($charge->no_service_done) }}</span></li>
                                    <li>{{ __('Total') }}:
                                        ({{ $charge->per_service_amount }}*{{ $charge->no_service_done }}) :
                                        <span>{{ Helpers::covertDefaultExchangeRate($charge->total) }}</span></li>
                                    {{-- <strong>{{ __('Total') }}per service amount{{ $charge->per_service_amount }}* no of service done{{ $charge->no_service_done }} :</strong> {{ Helpers::covertDefaultExchangeRate($charge->total) }}<br> --}}
                                </ul>
                                <div>
                                    @php
                                        $userRole = Helpers::getRoleByUserId(auth()->id());
                                    @endphp
                                    @if (in_array($userRole, [RoleEnum::ADMIN, RoleEnum::PROVIDER]) && $childBooking->booking_status->slug !== BookingEnumSlug::COMPLETED)
                                        <button class="btn delete-btn" type="submit" title="Delete Extra Charge">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    @empty
                        <p>{{ __('No extra charges found.') }}</p>
                    @endforelse
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/flatpickr.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('#servicemen').select2();
                var requiredServicemen = parseInt("{{ $childBooking->service->required_servicemen ?? 1 }}");
                var confirmBtn = $('#confirmPaymentBtn');
                $('#servicemen').select2({
                    placeholder: 'Select a serviceman',
                    allowClear: true,
                    minimumSelectionLength: requiredServicemen,
                });

               // Custom validation for exact servicemen selection
                $('#assignServicemanForm').on('submit', function(e) {
                    var selectedServicemen = $('#servicemen').val() || [];
                    if (selectedServicemen.length !== requiredServicemen) {
                        e.preventDefault();
                        $('.select2-error').remove();
                        $('#servicemen').closest('.select-dropdown').append(
                            '<span class="select2-error invalid-feedback d-block" role="alert">' +
                            '<strong>' + "{{ __('static.booking.select_exactly_servicemen', ['count' => '']) }}" + requiredServicemen + ' serviceman.</strong>' +                            '</span>'
                        );
                        return false;
                    } else {
                        $('.select2-error').remove();
                    }
                });

                // Clear error message when user changes selection
                $('#servicemen').on('change', function() {
                    $('.select2-error').remove();
                });

                var flatpickrInstance = flatpickr("#booking-date", {
                    altInput: true,
                    altFormat: "F j, Y H:i",
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    defaultDate: "{{ \Carbon\Carbon::parse($childBooking->date_time)->format('Y-m-d H:i') }}",
                    onChange: function(selectedDates, dateStr) {
                        if (dateStr) {
                            $("#confirmDateBtn").show();
                            $("#selected-date").val(dateStr);
                        }
                    }
                });

                $("#confirmDateBtn").click(function() {
                    $("#updateBookingForm").submit();
                });

                $('#paymentStatus').on('change', function() {
                    var paymentStatus = $(this).val();
                    var updateForm = $('#updatePaymentForm');

                    // Update hidden field with new payment status
                    $('#payment-status-field').val(paymentStatus);

                    // Disable form validation before modifying the select2
                    updateForm.validate().resetForm();

                    // Show the confirm button after validation
                    confirmBtn.show();

                    // Manually validate the form after modification
                    if (updateForm.valid()) {
                        confirmBtn.prop('disabled', false); // Enable confirm button if valid
                    } else {
                        confirmBtn.prop('disabled', true); // Disable confirm button if invalid
                    }
                });

                // Confirm button click event
                confirmBtn.on('click', function() {
                    var form = $('#updatePaymentForm');
                    form.submit();
                });

                // Click event handler for assign_serviceman button
                $("#assign_serviceman").click(function() {
                    var booking_id = "{{ $childBooking->id }}";
                    $.ajax({
                        url: "{{ route('backend.booking.getServicemen') }}",
                        type: "GET",
                        data: {
                            booking_id: booking_id,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: "json",
                        success: function(data) {
                            $("#servicemen").empty();
                            $.each(data, function(index, serviceman) {
                                $("#servicemen").append(
                                    $("<option></option>").val(serviceman.id)
                                    .text(serviceman.name)
                                );
                            });
                            $('#servicemen').trigger('change');
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching servicemen: ", error);
                        }
                    });

                });
                // Loop to remove '#' from class names
                var elements = document.getElementsByClassName('activity-dot');
                for (var i = 0; i < elements.length; i++) {
                    var element = elements[i];
                    var className = element.className;
                    className = className.replace('#', '');
                    element.className = className;
                }
            });
        })(jQuery);
    </script>
@endpush
