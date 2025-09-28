@use('App\Models\Booking')
@use('app\Helpers\Helpers')
@use('App\Enums\BookingEnum')
@use('App\Enums\SymbolPositionEnum')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\RoleEnum')

@php
    $role = Helpers::getRoleByUserId(request()->id);
    $bookingCounts = [];
    foreach (
        [
            BookingEnum::PENDING => 'box',
            BookingEnum::ON_GOING => 'calendar',
            BookingEnum::ON_THE_WAY => 'package',
            BookingEnum::COMPLETED => 'truck',
            BookingEnum::CANCEL => 'x-circle',
            BookingEnum::ON_HOLD => 'alert-circle',
        ]
        as $status => $icon
    ) {
        $bookingCounts[] = Booking::getBookingStatusById($user?->bookings, $user->id, $status);
    }

@endphp

@extends('backend.layouts.master')

@section('title', __('static.user_dashboard.general_info'))

@section('content')
    <div class="provider-dashboard-main-box">
        <div class="row g-sm-4 g-3">
            <div class="col-xxl-4 col-md-5">
                <div class="welcome-box">
                    <div class="top-box">
                        <img src="{{ asset('admin/images/welcome-shape.svg') }}" class="shape" alt="">
                    </div>
                    <div class="user-image">
                        <img src="{{ asset('admin/images/avatar/gradient-circle.svg') }}" class="circle" alt="">

                        @if ($user?->getFirstMediaUrl('image'))
                            <img class="img-fluid" src="{{ $user?->getFirstMediaUrl('image') }}" alt="header-user">
                        @else
                            <div class="initial-letter">{{ substr($user?->name, 0, 1) }}</div>
                        @endif
                        <i data-feather="check" class="check-icon"></i>
                    </div>

                    <div class="user-details">
                        <h3>{{ $user?->name }}</h3>
                    </div>

                    <ul class="person-info-list">
                        <li>
                            <span>{{  __('static.user_dashboard.email') }} :</span> {{ $user?->email }}
                        </li>
                        <li>
                            <span>{{  __('static.user_dashboard.phone') }} :</span> +{{ $user?->code }} {{ $user?->phone }}
                        </li>
                        <li>
                            <span>{{  __('static.user_dashboard.country') }} :</span> {{ $user?->primary_address?->country?->name }}
                        </li>
                        <li>
                            <span>{{  __('static.user_dashboard.state') }} :</span> {{ $user?->primary_address?->state?->name }}
                        </li>
                        <li>
                            <span>{{  __('static.user_dashboard.city') }} :</span> {{ $user?->primary_address?->city }}
                        </li>
                        <li>
                            <span>{{  __('static.user_dashboard.address') }} :</span> {{ $user?->primary_address?->address }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-xxl-8 col-md-7">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{  __('static.user_dashboard.bookings') }}</h4>
                        <a href="{{ route('backend.booking.index') }}">{{  __('static.user_dashboard.view_all') }}</a>

                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table recent-booking-table">
                                <thead>
                                    <tr>
                                        <th>{{  __('static.user_dashboard.booking_number') }}</th>
                                        <th>{{  __('static.user_dashboard.service') }}</th>
                                        <th>{{  __('static.user_dashboard.provider') }}</th>
                                        <th>{{  __('static.user_dashboard.created') }}</th>
                                        <th>{{  __('static.user_dashboard.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($user?->bookings->paginate(4) as $booking)
                                        <tr>
                                            <td>
                                                <span class="badge badge-booking">#{{ $booking?->booking_number }}</span>
                                            </td>
                                            <td>
                                                <div class="service-details-box">

                                                    <img src="{{ $booking?->service?->media?->first()?->getUrl() ?? asset('admin/images/service/1.png') }}"
                                                        class="img-fluid service-image" alt="">
                                                    <div class="service-details">
                                                        <h5>{{ $booking?->service->title }}</h5>
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            <h6>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($booking?->service->price, 2) }}</h6>
                                                        @else
                                                            <h6>{{ number_format($booking?->service->price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</h6>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="service-details-box">
                                                    @php
                                                        $media = $booking?->consumer?->getFirstMedia('image');
                                                        $imageUrl = $media ? $media->getUrl() : null;
                                                    @endphp

                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="Image"
                                                            class="img-fluid service-image rounded-circle">
                                                    @else
                                                        <div class="initial-letter">
                                                            {{ strtoupper(substr($booking?->consumer?->name, 0, 1)) }}
                                                        </div>
                                                    @endif

                                                    <div class="service-details">
                                                        <h5>{{ $booking?->consumer?->name }}</h5>
                                                        <h6>{{ $booking?->consumer?->email }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ date('d-M-Y', strtotime($booking->created_at)) }}</td>
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
                                            <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt="">
                                            <p>{{ __('static.dashboard.data_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="row g-sm-4 g-3">
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.serviceman.index') }}">
                        <div class="total-box color-1">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#servicemen') }}"></use>
                                </svg>
                                <div>
                                    <h4>{{ Helpers::getServicemenCountById($user->id) }}</h4>
                                    <h6>{{ __('static.dashboard.total_servicemen') }}</h6>
                                </div>
                            </div>

                            <div id="servicemen-chart"></div>

                            <div class="bottom-box down">

                            </div>
                        </div>
                        </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                     <a href="{{ route('backend.provider-wallet.index',['provider_id' => $user?->id]) }}">
                        <div class="total-box color-3">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#providers') }}">
                                    </use>
                                </svg>
                                <div>
                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                        <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getBalanceById($user->id) }}</h4>
                                    @else
                                        <h4>{{ Helpers::getBalanceById($user->id) }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                                    @endif
                                    <h6>{{ __('static.user_dashboard.wallet_balance') }}</h6>
                                </div>
                            </div>

                            <div id="provider-chart"></div>

                            <div class="bottom-box up"></div>
                        </div>
                    </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.service.index') }}">
                        <div class="total-box color-3">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#service') }}">
                                    </use>
                                </svg>
                                <div>
                                    <h4>{{ Helpers::getServicesCountById($user->id) }}</h4>
                                    <h6>{{ __('static.dashboard.services') }}</h6>
                                </div>
                            </div>

                            <div id="service-chart"></div>

                            <div class="bottom-box up"></div>
                        </div>
                        </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.review.index') }}">
                        <div class="total-box color-4">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#reviews') }}">
                                    </use>
                                </svg>
                                <div>
                                    <h4>{{ Helpers::getReviewsCountById($user->id) }}</h4>
                                    <h6>{{ __('static.dashboard.reviews') }}</h6>
                                </div>
                            </div>

                            <div id="review-chart"></div>

                            <div class="bottom-box up"></div>
                        </div>
                    </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.commission.index') }}">
                        <div class="total-box color-5">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#online-payment') }}">
                                    </use>
                                </svg>
                                <div>
                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)    
                                        <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getPayment($user?->id,null) }}</h4>
                                    @else
                                        <h4>{{ Helpers::getPayment($user?->id,null) }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                                    @endif
                                    <h6>{{ __('static.dashboard.online_payment') }}</h6>
                                </div>
                            </div>

                            <div id="onlinePayment-chart"></div>

                            <div class="bottom-box down"></div>
                        </div>
                        </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.commission.index') }}">
                        <div class="total-box color-6">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#offline-payment') }}">
                                    </use>
                                </svg>
                                <div>
                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                        <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getPayment($user?->id,'cash') }}</h4>
                                    @else
                                        <h4>{{ Helpers::getPayment($user?->id,'cash') }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                                    @endif
                                    <h6>{{ __('static.dashboard.offline_payment') }}</h6>
                                </div>
                            </div>

                            <div id="offline-payment-chart"></div>

                            <div class="bottom-box up"></div>
                        </div>
                        </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.booking.index') }}">
                        <div class="total-box color-7">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#booking') }}">
                                    </use>
                                </svg>
                                <div>
                                    <h4>{{ Helpers::getBookingsCountById($user->id) }}</h4>
                                    <h6>{{ __('static.dashboard.booking') }}</h6>
                                </div>
                            </div>

                            <div class="progress-box">
                                <div class="progress progress-info">
                                    <div class="progress-bar" style="width: 75%">
                                    </div>
                                </div>
                            </div>

                            <div class="bottom-box down"></div>
                        </div>
                        </a>
                    </div>

                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-sm-4">
                    <a href="{{ route('backend.withdraw-request.index') }}">

                        <div class="total-box color-1">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#customers') }}">
                                    </use>
                                </svg>
                                <div>
                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                        <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getWithdrawAmountById($user?->id) }}</h4>   
                                    @else
                                        <h4>{{ Helpers::getWithdrawAmountById($user?->id) }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>   
                                    @endif
                                    <h6>{{ __('static.user_dashboard.withdraw_amount') }}</h6>
                                </div>
                            </div>

                            <div id="customers-chart"></div>

                            <div class="bottom-box up"></div>
                        </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="dashboard-card h-auto">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.booking_status') }}</h4>
                    </div>

                    <div class="card-body">
                        <div id="booking-status-chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.servicemen') }}</h4>
                        <a href="{{ route('backend.serviceman.index') }}">{{ __('static.user_dashboard.view_all') }}</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table top-service-table table-height">
                                <thead>
                                    <tr>
                                        <th>{{ __('static.user_dashboard.service') }}</th>
                                        <th class="text-center">{{ __('static.user_dashboard.provider') }}</th>
                                        <th>{{ __('static.user_dashboard.created') }}</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($user?->servicemans->paginate(4) as $serviceman)
                                        <tr>
                                            <td>
                                                <div class="service-details-box">
                                                    @php
                                                        $media = $serviceman?->getFirstMedia('image');
                                                        $imageUrl = $media ? $media->getUrl() : null;
                                                    @endphp

                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="Image"
                                                            class="img-fluid service-image">
                                                    @else
                                                        <div class="initial-letter">
                                                            {{ strtoupper(substr($serviceman?->name, 0, 1)) }}</div>
                                                    @endif

                                                    <div class="service-details">
                                                        <h5>{{ $serviceman?->name }}</h5>
                                                        <h6>{{ $serviceman?->email }}</h6>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                {{ $serviceman->experience_duration }}
                                                {{ $serviceman->experience_interval }}
                                            </td>
                                            <td>{{ date('d-M-Y', strtotime($serviceman->created_at)) }}</td>
                                        </tr>
                                    @empty
                                        <div class="no-table-data">
                                            <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt="">
                                            <p>{{ __('static.dashboard.data_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.documents_list') }}</h4>
                        <a href="{{ route('backend.provider-document.index') }}">{{ __('static.user_dashboard.view_all') }}</a>

                    </div>
                    <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table documents-list-table">
                            <thead>
                                <tr>
                                    <th>{{ __('static.user_dashboard.document_no') }}</th>
                                    <th>{{ __('static.user_dashboard.image') }}</th>
                                    <th>{{ __('static.user_dashboard.document') }}</th>
                                    <th>{{ __('static.user_dashboard.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @forelse ($user?->UserDocuments->paginate(4) as $document)
                                        <tr>
                                            <td>
                                                <span class="badge badge-booking">#{{ $document?->identity_no }}</span>
                                            </td>
                                            <td> @php

                                                $media = $document?->getFirstMedia('provider_documents');
                                            @endphp

                                                @if ($media)
                                                    <img src="{{ $media?->getUrl() }}" alt="Image"
                                                        class="img-fluid card-image">
                                                @else
                                                    <img src="{{ asset('admin/images/No-image-found.jpg') }}"
                                                        alt="Placeholder Image" class="img-thumbnail img-fix">
                                                @endif
                                            </td>
                                            <td>
                                                {{ $document->document->title }}
                                            </td>
                                            <td>
                                                @php
                                                    $labelClass = '';
                                                    switch ($document->status) {
                                                        case 'approved':
                                                            $labelClass = 'success';
                                                            break;
                                                        case 'pending':
                                                            $labelClass = 'warning';
                                                            break;
                                                        case 'rejected':
                                                            $labelClass = 'danger';
                                                            break;
                                                    }
                                                @endphp
                                                <span
                                                    class="badge badge-{{ $labelClass }}-light">{{ ucfirst($document->status) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <div class="no-table-data">
                                            <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt="">
                                            <p>{{ __('static.dashboard.data_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7 col-md-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.reviews') }}</h4>
                        <a href="{{ route('backend.review.index') }}">{{ __('static.user_dashboard.view_all') }}</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive table-height">
                            <table class="table provider-reviews-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('static.dashboard.consumer') }}</th>
                                        <th>{{ __('static.dashboard.service') }}</th>
                                        <th>{{ __('static.dashboard.ratings') }}</th>
                                        <th>{{ __('static.user_dashboard.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($user->reviews as $review)
                                        <tr>
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
                                                            {{ strtoupper(substr($review?->consumer?->name, 0, 1)) }}</div>
                                                    @endif

                                                    <div class="service-details">
                                                        <h5>{{ $review?->consumer?->name }}</h5>
                                                        <h6>{{ $review?->consumer?->email }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="service-details-box">
                                                    <img src="{{ $booking?->service?->media?->first()?->getUrl() ?? asset('admin/images/service/1.png') }}"
                                                        class="img-fluid service-image" alt="">
                                                    <div class="service-details">
                                                        <h5>{{ $booking?->service->title }}</h5>
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            <h6>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($booking?->service->price, 2) }}</h6>
                                                        @else
                                                            <h6>{{ number_format($booking?->service->price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</h6>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="rating">
                                                    <img src="{{ asset('admin/images/svg/star.svg') }}" class="img-fluid"
                                                        alt="">
                                                    <h6>{{ number_format($review?->rating, 2) }}</h6>
                                                </div>
                                            </td>
                                            <td>
                                                <p>{{ $review?->description }}</p>
                                            </td>
                                        </tr>
                                    @empty
                                        <div class="no-table-data">
                                            <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt="">
                                            <p>{{ __('static.dashboard.data_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5 col-md-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.withdraw_request') }}</h4>
                        <a href="{{ route('backend.withdraw-request.index') }}">{{ __('static.user_dashboard.view_all') }}</a>

                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table top-service-table table-height">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{ __('static.dashboard.amount') }}</th>
                                        <th>{{ __('static.user_dashboard.message') }}</th>
                                        <th>{{ __('static.user_dashboard.status') }}</th>
                                        <th>{{ __('static.user_dashboard.created') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($user?->providerWithdrawRequest as $withdrawRequest)
                                    <tr>
                                        <td>{{ $withdrawRequest?->amount }}</td>
                                        <td>{{ $withdrawRequest?->message }}</td>
                                        <td>
                                            @php
                                                switch ($withdrawRequest->status) {
                                                    case 'approved':
                                                        $labelClass = 'success';
                                                        break;
                                                    case 'pending':
                                                        $labelClass = 'warning';
                                                        break;
                                                    case 'rejected':
                                                        $labelClass = 'danger';
                                                        break;
                                                    default:
                                                        $labelClass = 'warning';
                                                        break;
                                                }
                                            @endphp
                                            <span class="badge badge-{{ $labelClass}}-light">{{ ucfirst($withdrawRequest->status)}}</span>

                                        </td>
                                        <td>{{ $withdrawRequest?->created_at }}</td>
                                    </tr>
                                    @empty
                                        <div class="no-table-data">
                                            <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                alt="">
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
@endsection

@push('js')
    <script src="{{ asset('admin/js/apex-chart.js') }}"></script>
    <script src="{{ asset('admin/js/provider-custom-apexchart.js') }}"></script>
    <script>
            var bookingCounts = <?php echo json_encode($bookingCounts); ?>;
            var totalBooking = <?php echo  Helpers::getBookingsCountById($user->id); ?>;
      
            var bookingStatusChart = {
                labels: [
                    "{{ __('static.user_dashboard.pending') }}", 
                    "{{ __('static.user_dashboard.on_going') }}", 
                    "{{ __('static.user_dashboard.on_the_way') }}", 
                    "{{ __('static.user_dashboard.completed') }}", 
                    "{{ __('static.user_dashboard.cancel') }}", 
                    "{{ __('static.user_dashboard.on_hold') }}"
                ],
                series: bookingCounts,
                chart: {
                    type: "donut",
                    height: 375,
                    animations: {
                        enabled: true,
                        easing: "easeinout",
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150,
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350,
                        },
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    position: "bottom",
                    fontSize: "14px",
                    fontFamily: "var(--font-family)",
                    fontWeight: 500,
                    labels: {
                        colors: "#00162ecc",
                    },
                    markers: {
                        width: 13,
                        height: 13,
                    },
                },
                stroke: {
                    show: true,
                    width: 6,
                    colors: ['#F4F4F4'],
                    lineCap: 'round'
                },
                fill: {
                    type: 'solid'
                },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        donut: {
                            size: "80%",
                            labels: {
                                show: true,
                                name: {
                                    offsetY: 0,
                                },
                                total: {
                                    show: true,
                                    fontSize: "20px",
                                    fontFamily: "var(--font-family)",
                                    fontWeight: 600,
                                    label: totalBooking,
                                    formatter: () => "Total Bookings",
                                },
                            },
                        },
                    },
                },
                states: {
                    normal: {
                        filter: {
                            type: "none",
                        },
                    },
                    hover: {
                        filter: {
                            type: "none",
                        },
                    },
                    active: {
                        allowMultipleDataPointsSelection: false,
                        filter: {
                            type: "none",
                        },
                    },
                },
                colors: ["var(--primary-color)", "#FFBC58", "#27AF4D", "#AD46FF", "#00A8FF", "#FF4B4B"],
            };

            var bookingStatusChart = new ApexCharts(document.querySelector("#booking-status-chart"), bookingStatusChart);
            bookingStatusChart.render();
    </script>
@endpush
