@extends('backend.layouts.master')

@section('title', __('static.booking.details'))

@section('content')
@use('app\Helpers\Helpers')
    <div class="row g-sm-4 g-2">
        <div class="col-12">
            <div class="card tab2-card">
                <div class="card-header d-flex align-items-center gap-2 justify-content-between">
                    <div>
                        <h5>{{ __('static.booking.details') }} #{{ $booking->booking_number }}</h5>
                        <span>{{ __('static.booking.created_at') }}{{ $booking->created_at->format('j F Y, g:i A') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <form route="backend.update.settings" method="PUT" enctype="multipart/form-data"
                        class="needs-validation user-add">
                        <div class="sub-booking-table">
                            <div class="table-responsive service-detail custom-scrollbar">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{__('static.booking.sub_booking_no')}}</th>
                                            <th>{{__('static.booking.service_title')}}</th>
                                            <th>{{__('static.booking.service_provider')}}</th>
                                            <th>{{__('static.booking.service_rate')}}</th>
                                            <th>{{__('static.booking.sub_booking_details')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $subBookings = $booking->sub_bookings;

                                            if (auth()->check() && auth()->user()->hasRole('provider')) {
                                                $subBookings = $subBookings->where('provider_id', auth()->id());
                                            }
                                        @endphp
                                        @forelse ($subBookings as $sub_booking)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-booking">#{{ $sub_booking?->booking_number }}</span>
                                                </td>
                                                <td>{{ $sub_booking?->service?->title }}</td>
                                                <td>{{ $sub_booking?->provider?->name }}</td>
                                                <td>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $sub_booking?->service?->service_rate }}</td>
                                                <td>
                                                    <a href="{{ route('backend.booking.showChild', $sub_booking->id) }}" class="booking-icon show-icon">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <div class="d-flex flex-column no-data-detail">
                                                    <img class="mx-auto d-flex" src="{{ asset('admin/images/svg/no-data.svg') }}" alt="no-image">
                                                    <div class="data-not-found">
                                                        <span>{{__('static.data_not_found')}}</span>
                                                    </div>
                                                </div>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-4">
                <div class="col-xl-6 col-12">
                    <div class="booking-detail-2 card">
                        <div class="card-header primary-bg-color text-white">
                            <h4 class="mb-0">{{__('static.consumer_details')}}</h4>
                        </div>
                        <div class="provider-details-box">
                            <ul class="list-unstyled mb-0">
                                <li>{{__('static.booking.consumer_name')}} : <span>{{ $booking?->consumer?->name }}</span></li>
                                <li>{{__('static.phone')}} : <span>+{{ $booking?->consumer?->code . ' ' . $booking?->consumer?->phone }}</span></li>
                                <li>{{__('static.country')}} : <span> {{ $booking->consumer?->getPrimaryAddressAttribute()?->country?->name }}</span></li>
                                <li>{{__('static.state')}} : <span>{{ $booking->consumer?->getPrimaryAddressAttribute()?->state?->name }}</span></li>
                                <li>{{__('static.city')}} : <span>{{ $booking->consumer?->getPrimaryAddressAttribute()?->city }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-12">
                    <div class="booking-detail-2 card">
                        <div class="card-header primary-bg-color text-white">
                            <h4 class="mb-0">{{__('static.summary')}}</h4>
                        </div>
                        <div class="provider-details-box">
                            <ul class="list-unstyled mb-0">
                                <li>{{__('static.booking.payment_method')}} :<span>{{ $booking->payment_method }}</span></li>
                                <li>{{__('static.booking.payment_status')}} :<span>{{ $booking->payment_status }}</span></li>
                                <li>{{__('static.booking.total_extra_servicemen')}} :<span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->total_extra_servicemen ?? 0 }}</span></li>
                                <li>{{__('static.booking.total_servicemen_charge')}} :<span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->total_serviceman_charge ?? 0 }}</span></li>
                                <li>{{__('static.booking.coupon_discount')}} :<span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->coupon_total_discount ?? 0 }}</span></li>
                                <li>{{__('static.tax_total')}} :<span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->tax ?? 0 }}</span></li>
                                <li>{{__('static.sub_total')}} :<span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->subtotal ?? 0 }}</span></li>
                                @if (isset($booking->platform_fees))
                                    <li>{{__('static.settings.platform_fees')}} : <span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->platform_fees }}</span></li>
                                @endif
                                <li>{{__('static.total')}} :<span>{{Helpers::getSettings()['general']['default_currency']->symbol}} {{ $booking->total }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/vectormap.min.js') }}"></script>
    <script src="{{ asset('admin/js/vectormap.js') }}"></script>
    <script src="{{ asset('admin/js/vectormapcustom.js') }}"></script>
@endpush
