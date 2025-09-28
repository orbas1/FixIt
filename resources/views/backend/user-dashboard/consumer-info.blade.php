@use('App\Models\Booking')
@use('app\Helpers\Helpers')
@use('App\Enums\BookingEnum')
@use('App\Enums\SymbolPositionEnum')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\RoleEnum')

@php
    $role = Helpers::getRoleByUserId(request()->id);
    $bookingCounts = [];
foreach ([BookingEnum::PENDING => 'box', BookingEnum::ON_GOING => 'calendar',
          BookingEnum::ON_THE_WAY => 'package', BookingEnum::COMPLETED => 'truck',
          BookingEnum::CANCEL => 'x-circle', BookingEnum::ON_HOLD => 'alert-circle'] as $status => $icon) {

    $bookingCounts[] = Booking::getBookingStatusById($user?->consumerBookings?->whereNotNull('parent_id'), $user->id, $status);
}
@endphp

@extends('backend.layouts.master')

@section('title', __('static.user_dashboard.general_info'))

@section('content')
    <div class="provider-dashboard-main-box">
        <div class="row g-sm-4 g-3">
            <div class="col-xxl-4 col-lg-5 col-md-6">
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
                            <span>{{ __('static.user_dashboard.email') }}  :</span> {{ $user?->email }}
                        </li>
                        <li>
                            <span>{{ __('static.user_dashboard.phone') }} :</span> +{{ $user?->code }} {{ $user?->phone }}
                        </li>
                        <li>
                            <span>{{ __('static.user_dashboard.country') }} :</span> {{ $user?->primary_address?->country?->name }}
                        </li>
                        <li>
                            <span>{{ __('static.user_dashboard.state') }} :</span> {{ $user?->primary_address?->state?->name }}
                        </li>
                        <li>
                            <span>{{ __('static.user_dashboard.city') }} :</span> {{ $user?->primary_address?->city }}
                        </li>
                        <li>
                            <span>{{ __('static.user_dashboard.address') }} :</span> {{ $user?->primary_address?->address }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-xxl-8 col-lg-7 col-md-6">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.bookings') }}</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive table-height custom-scrollbar">
                            <table class="table consumer-recent-booking-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('static.user_dashboard.booking_number') }}</th>
                                        <th>{{ __('static.user_dashboard.service_name') }}</th>
                                        <th>{{ __('static.user_dashboard.provider_name') }}</th>
                                        <th>{{ __('static.user_dashboard.created') }}</th>
                                        <th>{{ __('static.user_dashboard.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($user?->consumerBookings?->whereNotNull('parent_id') as $booking)
                                        <tr>
                                            <td>
                                                <span class="badge badge-booking">#{{ $booking?->booking_number }}</span>
                                            </td>
                                            <td>
                                                <div class="service-details-box">
                                                    <img src="{{ $booking?->service?->media?->first()?->getUrl() ?? asset('admin/images/service/1.png') }}" class="img-fluid service-image" alt="">
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
                                                            $media = $booking?->consumer?->getFirstMedia('image');
                                                            $imageUrl = $media ? $media->getUrl() : null;
                                                        @endphp

                                                        @if ($imageUrl)
                                                            <img src="{{ $imageUrl }}" alt="Image" class="img-fluid service-image rounded-circle">
                                                        @else
                                                            <div class="initial-letter">{{ strtoupper(substr($booking?->consumer?->name, 0, 1)) }}</div>
                                                        @endif

                                                    <div class="service-details">
                                                        <h5>{{ $booking?->consumer?->name }}</h5>
                                                        <h6>{{  $booking?->consumer?->email }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ date('d-M-Y', strtotime($booking->created_at)) }}</td>
                                            <td>
                                            @if (count($booking->sub_bookings))
                                                    <span class="badge booking-status-{{ $booking->sub_bookings?->first()?->booking_status?->color_code }}">{{ $booking->sub_bookings?->first()?->booking_status?->name }}</span>
                                                @elseif (isset($booking->booking_status?->color_code))
                                                    <span class="badge booking-status-{{ $booking->booking_status?->color_code }}">{{ $booking->booking_status?->name }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-4">
                <div class="row g-sm-4 g-3">
                    <div class="col-lg-12 col-sm-6">
                        <div class="total-box color-1">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#servicemen') }}"></use>
                                </svg>
                                <div>
                                <h4>{{ Helpers::getBookingsCountById($user->id) }}</h4>
                                <h6>{{ __('static.user_dashboard.bookings') }}</h6>
                                </div>
                            </div>

                            <div id="servicemen-chart"></div>

                            <div class="bottom-box down">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 col-sm-6">
                        <div class="total-box color-5">
                            <div class="top-box">
                                <svg>
                                    <use xlink:href="{{ asset('admin/images/svg/total-service.svg#online-payment') }}">
                                    </use>
                                </svg>
                                <div>
                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                    <h4>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::getBalanceById($user->id) }}</h4>
                                @else
                                    <h4>{{ Helpers::getBalanceById($user->id) }} {{ Helpers::getDefaultCurrencySymbol() }}</h4>
                                @endif
                                <h6>{{ __('static.user_dashboard.wallet') }}</h6>
                                </div>
                            </div>

                            <div id="onlinePayment-chart"></div>

                            <div class="bottom-box down">

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-6 col-lg-12 col-md-6 order-xxl-1 order-md-3">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4>{{ __('static.user_dashboard.reviews') }}</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table consumer-reviews-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('static.user_dashboard.customer_name') }}</th>
                                        <th>{{ __('static.user_dashboard.service_name') }}</th>
                                        <th>{{ __('static.user_dashboard.ratings') }}</th>
                                        <th>{{ __('static.user_dashboard.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($user->consumerReviews->whereNotNull('service_id')->paginate(4) as $review)
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
                                                        <h6>{{  $review?->consumer?->email }}</h6>
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
                                                <h6>{{ number_format($review?->rating , 2) }}</h6>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="reviews">{{ $review?->description }}</p>
                                        </td>
                                    </tr>
                                    @empty
                                    <div class="no-table-data">
                                                <img src="{{ asset('admin/images/no-table-data.svg') }}" class="img-fluid"
                                                    alt="">
                                                <p>{{ __('static.user_dashboard.data_not_found') }}</p>
                                            </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-8 col-md-6 order-xxl-3 order-md-1">
                <div class="dashboard-card">
                    <div class="card-title">
                        <h4><h4>{{ __('static.user_dashboard.booking_status') }}</h4></h4>
                    </div>

                    <div class="card-body">
                        <div id="booking-status-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/apex-chart.js') }}"></script>
    {{-- <script src="{{ asset('admin/js/provider-custom-apexchart.js') }}"></script> --}}
    <script>
        /* Servicemen Chart */
        var servicemenChart = {
            series: [{
                name: "Desktops",
                data: [41, 91, 40, 80, 62, 69, 91]
            }],
            stroke: {
                width: 3
            },
            chart: {
                height: 74,
                type: 'line',
                toolbar: {
                    show: false,
                },
                zoom: {
                    enabled: false
                }
            },
            xaxis: {
                show: false,
                labels: {
                    show: false,
                }
            },

            yaxis: {
                show: false,
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
        enabled: false
    },
            grid: {
                show: false,
                padding: {
                    top: -10,
                    bottom: -10,
                },
            },
            markers: {
                size: 5,
            },
        };
        var servicemenChart = new ApexCharts(document.querySelector("#servicemen-chart"), servicemenChart);
        servicemenChart.render();
        /* Servicemen Chart */

        /* Online Payment Chart */
        var onlinePaymentChart = {
            series: [{
                name: "Month",
                data: [0, 15, 15, 10, 10, 20, 20, 25, 25],
            }, ],
            chart: {
                type: "area",
                height: 73,
                toolbar: {
                    show: false,
                },
            },
            stroke: {
                curve: "straight",
                width: 3,
            },
            tooltip: {
        enabled: false
    },
            xaxis: {
                type: "category",
                categories: ["jan", "feb", "mar", "apr", "may", "jun", "july", "aug", "sep", "oct"],
                labels: {
                    show: false,
                },
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                tooltip: {
                    enabled: false,
                },
            },
            grid: {
                show: false,
                padding: {
                    top: -20,
                    bottom: -20,
                    left: -10,
                    right: -10
                },
            },
            yaxis: {
                show: false,
            },
            dataLabels: {
                enabled: false,
            },
            markers: {
                discrete: [{
                    seriesIndex: 0,
                    dataPointIndex: 7,
                    fillColor: "#27AF4D",
                    strokeColor: "#fff",
                    size: 6,
                    sizeOffset: 3,
                }, ],
                hover: {
                    size: 5,
                    sizeOffset: 0,
                },
            },
            colors: ["#27AF4D"],
            fill: {
                type: "gradient",
                gradient: {
                    shade: "light",
                    type: "vertical",
                    shadeIntensity: 0.1,
                    inverseColors: true,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 200],
                },
            },
        };
        var onlinePaymentChart = new ApexCharts(document.querySelector("#onlinePayment-chart"), onlinePaymentChart);
        onlinePaymentChart.render();

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
                                    formatter: () => "{{ __('static.user_dashboard.total_bookings') }}",
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
