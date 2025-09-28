@use('App\Helpers\Helpers')
@use('App\Models\Setting')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\RoleEnum')
@use('App\Enums\UserTypeEnum')
@php
    $settings = Setting::first()->values;
    $locale = Session::get('locale', app()->getLocale());
@endphp
<!-- Page Sidebar Start-->

@if(Request::is('backend/booking/create'))
<div class="page-sidebar open">
@else
<div class="page-sidebar">
@endif
{{-- sidebar .sidebar-menu --}}
    <span class="cursor-pointer sidebar-button" id="sidebar-toggle-btn">
        <i data-feather="chevron-right" id="sidebar-toggle"></i>
    </span>
    <div class="main-header-left">
        <div class="logo-wrapper">
            <a href="{{ route('backend.dashboard') }}" class="logo-img">
                <img class="blur-up lazyloaded img-fluid" alt="site-logo" src="{{ asset($settings['general']['light_logo']) ?? asset('admin/images/Logo-Light.png') }}">
            </a>
            <a href="{{ route('backend.dashboard') }}" class="favicon-img">
                <img class="blur-up lazyloaded img-fluid" alt="site-logo" src="{{ asset($settings['general']['favicon']) ?? asset('admin/images/faviconIcon.png') }}">
            </a>
        </div>
    </div>
    <div class="sidebar">
        <ul class="sidebar-menu custom-scrollbar" id="sidebar-menu">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h6>Pinned</h6>
                </div>
            </li>
            <li class="sidebar-main-title">
                <div>
                    <h6>{{ __('static.dashboard.dashboard') }}</h6>
                </div>
            </li>
            <li>
                <i class="ri-pushpin-2-line"></i>
                <a href="{{ route('backend.dashboard') }}"
                    class="sidebar-header {{ Request::is('backend/dashboard*') ? 'active' : '' }}">
                    <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/home-line.svg') }}">
                    <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/home-fill.svg') }}">
                    <span>{{ __('static.dashboard.dashboard') }}</span>
                </a>
            </li>
            @canAny(['backend.user.index', 'backend.serviceman_withdraw_request.index', 'backend.role.index',
                'backend.serviceman.index', 'backend.serviceman_wallet.index', 'backend.provider.index',
                'backend.provider_document.index', 'backend.provider_time_slot.index', 'backend.provider_wallet.index',
                'backend.withdraw_request.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.user_management') }}</h6>
                    </div>
                </li>
                @canAny(['backend.user.index', 'backend.role.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/user*') || Request::is('backend/role*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-fill.svg') }}">
                            <span>{{ __('static.users.system_users') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/user*') || Request::is('backend/role*') ? 'menu-open' : '' }}">
                            @can('backend.user.index')
                                <li>
                                    <a href="{{ route('backend.user.index') }}"
                                        class="{{ Request::is('backend/user') && !Request::is('backend/user/create') ? 'active' : '' }}">{{ __('static.users.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.users.create')
                                <li>
                                    <a href="{{ route('backend.user.create') }}"
                                        class="{{ Request::is('backend/user/create') ? 'active' : '' }}">{{ __('static.users.create') }}</a>
                                </li>
                            @endcan
                            @can('backend.role.index')
                                <li>
                                    <a href="{{ route('backend.role.index') }}"
                                        class="{{ Request::is('backend/role*') || Request::is('backend/role/create') ? 'active' : '' }}">{{ __('static.role') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanAny
                @canAny(['backend.customer.index', 'backend.wallet.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/customer*') || Request::is('backend/wallet*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-fill.svg') }}">
                            <span>{{ __('static.customer.customers') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/customer*') || Request::is('backend/wallet*') ? 'menu-open' : '' }}">
                            @can('backend.customer.index')
                                <li>
                                    <a href="{{ route('backend.customer.index') }}"
                                        class="{{ Request::is('backend/customer') && !Request::is('backend/customer/create') ? 'active' : '' }}">{{ __('static.customer.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.customer.create')
                                <li>
                                    <a href="{{ route('backend.customer.create') }}"
                                        class="{{ Request::is('backend/customer/create') ? 'active' : '' }}">{{ __('static.customer.create') }}</a>
                                </li>
                            @endcan
                            @can('backend.wallet.index')
                                <li>
                                    <a href="{{ route('backend.wallet.index') }}"
                                        class="{{ Request::is('backend/wallet*') ? 'active' : '' }}">{{ __('static.wallet.wallet') }}</a>
                                </li>
                            @endcan
                            @can('backend.unverified_user.index')
                                <li>
                                    <a href="{{ route('backend.unverfied-users.index', ['role' => RoleEnum::CONSUMER]) }}"
                                        class="{{ Request::is('backend/unverfied-users*') ? 'active' : '' }}">{{ __('static.unverfied_users.unverfied_consumer') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanAny

                @canAny(['backend.commission_history.index', 'backend.provider.index', 'backend.provider_document.index', 'backend.provider_time_slot.index', 'backend.provider_wallet.index', 'backend.withdraw_request.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ (!Request::is('backend/providerSiteService*') && Request::is('backend/provider*') && !Request::is('backend/provider-report')) || Request::is('backend/commission*') || Request::is('backend/withdraw-request*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-fill.svg') }}">
                            <span>{{ __('static.provider.providers') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ (!Request::is('backend/providerSiteService*') && Request::is('backend/provider*') && !Request::is('backend/provider-report')) || Request::is('backend/withdraw-request*') || Request::is('backend/commission*') ? 'menu-open' : '' }}">
                            @can('backend.provider.index')
                                <li>
                                    <a href="{{ route('backend.provider.index') }}"
                                        class="{{ !Request::is('backend/providerSiteService*') && Request::is('backend/provider') && !Request::is('backend/provider/create') && !Request::is('backend/provider-document*') ? 'active' : '' }}">{{ __('static.provider.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.provider.create')
                                <li>
                                    <a href="{{ route('backend.provider.create') }}"
                                        class="{{ Request::is('backend/provider/create') ? 'active' : '' }}">{{ __('static.provider.create') }}</a>
                                </li>
                            @endcan
                            @can('backend.provider_wallet.index')
                                <li>
                                    <a href="{{ route('backend.provider-wallet.index') }}"
                                        class="{{ Request::is('backend/provider-wallet*') ? 'active' : '' }}">{{ __('static.wallet.wallet') }}</a>
                                </li>
                            @endcan
                            @can('backend.provider_document.index')
                                <li>
                                    <a href="{{ route('backend.provider-document.index') }}"
                                        class="{{ Request::is('backend/provider-document*') ? 'active' : '' }}">{{ __('static.provider_document.provider_documents') }}</a>
                                </li>
                            @endcan
                            @can('backend.provider_time_slot.index')
                                <li>
                                    <a href="{{ route('backend.provider-time-slot.index') }}"
                                        class="{{ Request::is('backend/provider-time-slot*') ? 'active' : '' }}">{{ __('static.provider_time_slot.provider_time_slot') }}</a>
                                </li>
                            @endcan
                            @can('backend.commission_history.index')
                                <li>
                                    <a href="{{ route('backend.commission.index') }}"
                                        class="{{ Request::is('backend/commission*') ? 'active' : '' }}">{{ __('static.commission_history.commission_history') }}</a>
                                </li>
                            @endcan
                            @can('backend.withdraw_request.index')
                                <li>
                                    <a href="{{ route('backend.withdraw-request.index') }}"
                                        class="{{ Request::is('backend/withdraw-request*') ? 'active' : '' }}">{{ __('static.withdraw.withdraw_request') }}</a>
                                </li>
                            @endcan
                            @can('backend.unverified_user.index')
                                <li>
                                    <a href="{{ route('backend.unverfied-users.index', ['role' => RoleEnum::PROVIDER]) }}"
                                        class="{{ Request::is('backend/unverified-users*') ? 'active' : '' }}">{{ __('static.unverfied_users.unverfied_provider') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanAny

                @if (auth()->user()->hasRole(RoleEnum::ADMIN) || (auth()->user()->hasRole(RoleEnum::PROVIDER) && auth()->user()->type === 'company')) 
                    @canAny(['backend.serviceman.index', 'backend.serviceman.create',
                                    'backend.serviceman_withdraw_request.index', 'backend.serviceman_wallet.index'])
                        <li>
                            <i class="ri-pushpin-2-line"></i>
                            <a href="javascript:void(0);"
                                class="sidebar-header {{ Request::is('backend/serviceman*') ? 'active' : '' }}">
                                <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-line.svg') }}">
                                <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/users-fill.svg') }}">
                                <span>{{ __('static.serviceman.servicemen') }}</span>
                                <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                            </a>
                            <ul class="sidebar-submenu {{ Request::is('backend/serviceman*') ? 'menu-open' : '' }}">
                                @can('backend.serviceman.index')
                                    <li>
                                        <a href="{{ route('backend.serviceman.index') }}"
                                            class=" {{ Request::is('backend/serviceman') ? 'active' : '' }}">{{ __('static.serviceman.all') }}</a>
                                    </li>
                                @endcan
                                @can('backend.serviceman.create')
                                    <li>
                                        <a href="{{ route('backend.serviceman.create') }}"
                                            class=" {{ Request::is('backend/serviceman/create') ? 'active' : '' }}">{{ __('static.serviceman.create') }}</a>
                                    </li>
                                @endcan
                                @can('backend.serviceman_wallet.index')
                                    <li>
                                        <a href="{{ route('backend.serviceman-wallet.index') }}"
                                            class="{{ Request::is('backend/serviceman-wallet*') ? 'active' : '' }}">{{ __('static.wallet.wallet') }}</a>
                                    </li>
                                @endcan
                                @can('backend.serviceman_withdraw_request.index')
                                    <li>
                                        <a href="{{ route('backend.serviceman-withdraw-request.index') }}"
                                            class="{{ Request::is('backend/serviceman-withdraw-request*') ? 'active' : '' }}">{{ __('static.withdraw.withdraw_request') }}</a>
                                    </li>
                                @endcan
                                @can('backend.serviceman_location.index')
                                    <li>
                                        <a href="{{ route('backend.serviceman-location.index') }}"
                                            class="{{ Request::is('backend/serviceman-location*') ? 'active' : '' }}">{{ __('static.serviceman.locations') }}</a>
                                    </li>
                                @endcan

                                @can('backend.unverified_user.index')
                                    <li>
                                        <a href="{{ route('backend.unverfied-users.index', ['role' => RoleEnum::SERVICEMAN]) }}"
                                            class="{{ Request::is('backend/unverfied-users*') ? 'active' : '' }}">{{ __('static.unverfied_users.unverfied_serviceman') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanAny
                @endif
                
            @endcan
            @can('backend.unverified_user.index')
                <li>
                    <i class="ri-pushpin-2-line"></i>
                    <a href="{{ route('backend.unverfied-users.index') }}"
                        class="sidebar-header {{ Request::is('backend/unverified-users*') ? 'active' : '' }}">
                        <img class="inactive-icon"
                            src="{{ asset('admin/images/svg/sidebar-icon/unverified-users-line.svg') }}">
                        <img class="active-icon"
                            src="{{ asset('admin/images/svg/sidebar-icon/unverified-users-fill.svg') }}">
                        <span>{{ __('static.unverfied_users.unverfied_users') }}</span>
                    </a>
                </li>
            @endcan

            @canAny(['backend.zone.index', 'backend.service-package.index', 'backend.service.index',
                'backend.service_category.index', 'backend.service_request.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.service_management') }}</h6>
                    </div>
                </li>
                @can('backend.zone.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/zone*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/blogs-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/blogs-fill.svg') }}">
                            <span>{{ __('static.zone.zones') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>

                        <ul
                            class="sidebar-submenu {{ Request::is('backend/zone*') ? 'menu-open' : '' }}">
                            @can('backend.booking.index')
                                <li>
                                    <a href="{{ route('backend.zone.index') }}"
                                        class="{{ Request::is('backend/zone') && !Request::is('backend/zone/create') ? 'active' : '' }}">{{ __('static.zone.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.zone.create')
                                <li>
                                    <a href="{{ route('backend.zone.create') }}"
                                        class="{{ Request::is('backend/zone/create') ? 'active' : '' }}">{{ __('static.zone.create') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @canAny(['backend.service-package.index', 'backend.service.index', 'backend.service_category.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ (Request::is('backend/service*') && !Request::is('backend/serviceman*') && !Request::is('backend/servicemen-review*') && !Request::is('backend/service-requests*')) || Request::is('backend/service-package*') || Request::is('backend/category*') || Request::is('backend/providerSiteService*') || Request::is('backend/additional-service*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/service-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/service-fill.svg') }}">
                            <span>{{ __('static.service.services') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ (Request::is('backend/service*') && !Request::is('backend/serviceman*') && !Request::is('backend/servicemen-review*') && !Request::is('backend/service-requests*')) || Request::is('backend/category*') || Request::is('backend/providerSiteService*') || Request::is('backend/additional-service*') ? 'menu-open' : '' }}">
                            @can('backend.service.index')
                                <li>
                                    <a href="{{ route('backend.service.index') }}"
                                        class="{{ Request::is('backend/service*') && !Request::is('backend/service/create') && !Request::is('backend/serviceman*') && !Request::is('backend/service-package*') && !Request::is('backend/servicemen-review*') && !Request::is('backend/service-requests*') ? 'active' : '' }}">{{ __('static.service.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.service.create')
                                <li>
                                    <a href="{{ route('backend.service.create') }}"
                                        class="{{ Request::is('backend/service/create') ? 'active' : '' }}">{{ __('static.service.create') }}</a>
                                </li>
                            @endcan
                            @can('backend.service_category.index')
                                <li>
                                    <a href="{{ route('backend.category.index') }}"
                                        class="{{ Request::is('backend/category*') ? 'active' : '' }}">{{ __('static.categories.categories') }}</a>
                                </li>
                            @endcan
                            @if (isset($settings['activation']['additional_services']) && $settings['activation']['additional_services'])
                                @can('backend.service.index')
                                    <li>
                                        <a href="{{ route('backend.additional-service.index') }}"
                                            class="{{ Request::is('backend/additional-service*') ? 'active' : '' }}">{{ __('static.additional_service.additional_services') }}</a>
                                    </li>
                                @endcan
                            @endif
                            @can('backend.service-package.index')
                                <li>
                                    <a href="{{ route('backend.service-package.index') }}"
                                        class="{{ Request::is('backend/service-package*') ? 'active' : '' }}">{{ __('static.service_package.service_packages') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanAny
                @can('backend.service_request.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.service-requests.index') }}"
                            class="sidebar-header {{ Request::is('backend/service-requests*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/global-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/global-fill.svg') }}">
                            <span>{{ __('static.service_request.service_requests') }}</span>
                        </a>
                    </li>
                @endcan
            @endcan

            @canAny(['backend.testimonial.index', 'backend.booking.index', 'backend.payment_method.index',
                'backend.review.index', 'backend.report.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.booking_management') }}</h6>
                    </div>
                </li>
                <li>
                    <i class="ri-pushpin-2-line"></i>
                    <a href="javascript:void(0);"
                        class="sidebar-header {{ Request::is('backend/booking*') && !Request::is('backend/booking-report') ? 'active' : '' }}">
                        <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/calendar-line.svg') }}">
                        <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/calendar-fill.svg') }}">
                        <span>{{ __('static.booking.bookings') }}</span>
                        <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                    </a>

                    <ul
                        class="sidebar-submenu {{ Request::is('backend/booking*') && !Request::is('backend/booking-report') ? 'menu-open' : '' }}">
                        @can('backend.booking.index')
                            <li>
                                <a href="{{ route('backend.booking.index') }}"
                                    class="{{ Request::fullUrlIs(route('backend.booking.index')) ? 'active' : '' }}">
                                    {{ __('static.booking.all') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::PENDING]) }}"
                                    class="{{ request('status') === BookingEnumSlug::PENDING ? 'active' : '' }}">
                                    {{ __('static.booking.pending') }}
                                </a>
                                <span class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::PENDING) }}</span>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ACCEPTED]) }}"
                                    class="{{ request('status') === BookingEnumSlug::ACCEPTED ? 'active' : '' }}">
                                    {{ __('static.booking.accepted') }}
                                </a>
                                <span
                                    class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::ACCEPTED) }}</span>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ASSIGNED]) }}"
                                    class="{{ request('status') === BookingEnumSlug::ASSIGNED ? 'active' : '' }}">
                                    {{ __('static.booking.assigned') }}
                                </a>
                                <span
                                    class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::ASSIGNED) }}</span>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ON_THE_WAY]) }}"
                                    class="{{ request('status') === BookingEnumSlug::ON_THE_WAY ? 'active' : '' }}">
                                    {{ __('static.booking.on_the_way') }}
                                </a>
                                <span
                                    class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::ON_THE_WAY) }}</span>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::ON_GOING]) }}"
                                    class="{{ request('status') === BookingEnumSlug::ON_GOING ? 'active' : '' }}">
                                    {{ __('static.booking.on_going') }}
                                </a>
                                <span
                                    class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::ON_GOING) }}</span>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::CANCEL]) }}"
                                    class="{{ request('status') === BookingEnumSlug::CANCEL ? 'active' : '' }}">
                                    {{ __('static.booking.cancel') }}
                                </a>
                                <span class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::CANCEL) }}</span>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking.index', ['status' => BookingEnumSlug::COMPLETED]) }}"
                                    class="{{ request('status') === BookingEnumSlug::COMPLETED ? 'active' : '' }}">
                                    {{ __('static.booking.completed') }}
                                </a>
                                <span
                                    class="badge">{{ Helpers::getBookingsCountByStatus(BookingEnumSlug::COMPLETED) }}</span>
                            </li>
                        @endcan
                    </ul>
                </li>

                @can('backend.payment_method.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.transaction.index') }}"
                            class="sidebar-header {{ Request::is('backend/transaction*') && !Request::is('backend/transaction-report') ? 'active' : '' }}">
                            <img class="inactive-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/transactions-line.svg') }}">
                            <img class="active-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/transactions-fill.svg') }}">
                            <span>{{ __('static.transaction.transactions') }}</span>
                        </a>
                    </li>
                @endcan

                @can('backend.review.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/review*') || Request::is('backend/servicemen-review') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/review-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/review-fill.svg') }}">
                            <span>{{ __('static.review.all') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/review*') || Request::is('backend/servicemen-review*') ? 'menu-open' : '' }}">
                            <li>
                                <a href="{{ route('backend.review.index') }}"
                                    class="{{ Request::is('backend/review*') ? 'active' : '' }}">{{ __('static.review.service_reviews') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('backend.servicemen-review') }}"
                                    class="{{ Request::is('backend/servicemen-review*') ? 'active' : '' }}">{{ __('static.review.serviceman_reviews') }}</a>
                            </li>
                        </ul>
                    </li>
                @endcan

                @can('backend.report.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/transaction-report') || Request::is('backend/booking-report') || Request::is('backend/provider-report') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/chart-2-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/chart-2-fill.svg') }}">
                            <span>{{ __('static.report.reports') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>

                        <ul
                            class="sidebar-submenu {{ Request::is('backend/transaction-report') || Request::is('backend/booking-report') || Request::is('backend/provider-report') ? 'menu-open' : '' }}">
                            <li>
                                <a href="{{ route('backend.transaction-report.index') }}"
                                    class="{{ Request::fullUrlIs(route('backend.transaction-report.index')) ? 'active' : '' }}">
                                    {{ __('static.report.transaction_reports') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('backend.booking-report.index') }}"
                                    class="{{ Request::fullUrlIs(route('backend.booking-report.index')) ? 'active' : '' }}">
                                    {{ __('static.report.booking_reports') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('backend.provider-report.index') }}"
                                    class="{{ Request::fullUrlIs(route('backend.provider-report.index')) ? 'active' : '' }}">
                                    {{ __('static.report.provider_reports') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            @endcan

            @canAny(['backend.coupon.index', 'backend.plan.index', 'backend.banner.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.marketing_advertising') }}</h6>
                    </div>
                </li>
                @can('backend.coupon.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.coupon.index') }}"
                            class="sidebar-header {{ Request::is('backend/coupon*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/coupon-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/coupon-fill.svg') }}">
                            <span>{{ __('static.coupon.coupons') }}</span>
                        </a>
                    </li>
                @endcan
                @if (Helpers::isModuleEnable('Subscription'))
                    @can('backend.plan.index')
                        <li>
                            <i class="ri-pushpin-2-line"></i>
                            <a href="javascript:void(0);"
                                class="sidebar-header  {{ Request::is('backend/plan*') || Request::is('backend/subscriptions*') ? 'active' : '' }}">
                                <img class="inactive-icon"
                                    src="{{ asset('admin/images/svg/sidebar-icon/transactions-line.svg') }}">
                                <img class="active-icon"
                                    src="{{ asset('admin/images/svg/sidebar-icon/transactions-fill.svg') }}">
                                <span>{{ __('static.plan.plans') }}</span>
                                <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                            </a>
                            <ul
                                class="sidebar-submenu {{ Request::is('backend/plan*') || Request::is('backend/subscriptions*') ? 'menu-open' : '' }}">
                                <li>
                                    <a href="{{ route('backend.plan.index') }}"
                                        class="{{ Request::is('backend/plan*') ? 'active' : '' }}">{{ __('static.plan.all') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.subscription.index') }}"
                                        class="{{ Request::is('backend/subscriptions*') ? 'active' : '' }}">{{ __('static.plan.subscriptions') }}</a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                @endif
                @can('backend.banner.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/banner*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/flag-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/flag-fill.svg') }}">
                            <span>{{ __('static.banner.banners') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul class="sidebar-submenu {{ Request::is('backend/banner*') ? 'menu-open' : '' }}">
                            @can('backend.banner.index')
                                <li>
                                    <a href="{{ route('backend.banner.index') }}"
                                        class="{{ Request::is('backend/banner') && !Request::is('backend/banner/create') ? 'active' : '' }}">{{ __('static.banner.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.banner.create')
                                <li>
                                    <a href="{{ route('backend.banner.create') }}"
                                        class="{{ Request::is('backend/banner/create') ? 'active' : '' }}">{{ __('static.banner.create') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('backend.advertisement.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/advertisement*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/ads-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/ads-fill.svg') }}">
                            <span>{{ __('static.advertisement.advertisement') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul class="sidebar-submenu {{ Request::is('backend/advertisement*') ? 'menu-open' : '' }}">
                            @can('backend.advertisement.index')
                                <li>
                                    <a href="{{ route('backend.advertisement.index') }}"
                                        class="{{ Request::is('backend/advertisement') && !Request::is('backend/advertisement/create') ? 'active' : '' }}">{{ __('static.advertisement.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.advertisement.create')
                                <li>
                                    <a href="{{ route('backend.advertisement.create') }}"
                                        class="{{ Request::is('backend/advertisement/create') ? 'active' : '' }}">{{ __('static.advertisement.create') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('backend.push_notification.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/notifications*') || Request::is('backend/push-notifications') ? 'active' : '' }}">
                            <img class="inactive-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/notification-line.svg') }}">
                            <img class="active-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/notification-fill.svg') }}">
                            <span>{{ __('static.notification.notifications') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/notifications') || Request::is('backend/push-notifications') ? 'menu-open' : '' }}">
                            @can('backend.push_notification.index')
                                <li>
                                    <a href="{{ route('backend.notifications') }}"
                                        class="{{ Request::is('backend/notifications*') && !Request::is('backend/push-notifications') ? 'active' : '' }}">{{ __('static.notification.list_notifications') }}</a>
                                </li>
                            @endcan
                            @can('backend.push_notification.create')
                                <li>
                                    <a href="{{ route('backend.push-notifications') }}"
                                        class="{{ Request::is('backend/push-notifications') ? 'active' : '' }}">{{ __('static.notification.send') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                @can('backend.news_letter.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.subscribers') }}"
                            class="sidebar-header  {{ Request::is('backend/subscribers') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/service-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/service-fill.svg') }}">
                            <span>{{ __('static.subscribers.subscribers') }}</span>
                        </a>
                    </li>
                @endcan
                @can('backend.testimonial.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.testimonial.index') }}"
                            class="sidebar-header  {{ Request::is('backend/testimonial') || Request::is('backend/testimonial/create') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/message-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/message-fill.svg') }}">
                            <span>{{ __('static.testimonials.testimonials') }}</span>
                        </a>
                    </li>
                @endcan
            @endcan

            @canAny(['backend.tax.index', 'backend.currency.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.financial_management') }}</h6>
                    </div>
                </li>
                @can('backend.tax.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.tax.index') }}"
                            class="sidebar-header {{ Request::is('backend/tax*') ? 'active' : '' }}">
                            <img class="inactive-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/percentage-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/percentage-fill.svg') }}">
                            <span>{{ __('static.tax.taxes') }}</span>
                        </a>
                    </li>
                @endcan
                @can('backend.currency.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.currency.index') }}"
                            class="sidebar-header {{ Request::is('backend/currency*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/dollar-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/dollar-fill.svg') }}">
                            <span>{{ __('static.currency.currencies') }}</span>
                        </a>
                    </li>
                @endcan
            @endcan

            @canAny(['backend.blog_category.index', 'backend.tag.index', 'backend.blog.index', 'backend.page.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.content_management') }}</h6>
                    </div>
                </li>
                @if (isset($settings['activation']['blogs_enable']) && $settings['activation']['blogs_enable'])
                    @canAny(['backend.blog_category.index', 'backend.tag.index', 'backend.blog.index'])
                        <li>
                            <i class="ri-pushpin-2-line"></i>
                            <a href="javascript:void(0);"
                                class="sidebar-header {{ Request::is('backend/tag*') || Request::is('backend/blog*') || Request::is('backend/blog-category*') ? 'active' : '' }}">
                                <img class="inactive-icon"
                                    src="{{ asset('admin/images/svg/sidebar-icon/coupon-line.svg') }}">
                                <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/coupon-fill.svg') }}">
                                <span>{{ __('static.blog.blogs') }}</span>
                                <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                            </a>
                            <ul
                                class="sidebar-submenu {{ Request::is('backend/blog*') || Request::is('backend/tag*') ? 'menu-open' : '' }}">
                                @can('backend.blog.index')
                                    <li>
                                        <a href="{{ route('backend.blog.index') }}"
                                            class="{{ Request::is('backend/blog') ? 'active' : '' }}">{{ __('static.blog.all') }}</a>
                                    </li>
                                @endcan
                                @can('backend.blog.create')
                                    <li>
                                        <a href="{{ route('backend.blog.create') }}"
                                            class="{{ Request::is('backend/blog/create') ? 'active' : '' }}">{{ __('static.blog.create') }}</a>
                                    </li>
                                @endcan
                                @can('backend.blog_category.index')
                                    <li>
                                        <a href="{{ route('backend.blog-category.index') }}"
                                            class="{{ Request::is('backend/blog-category*') ? 'active' : '' }}">{{ __('static.categories.categories') }}</a>
                                    </li>
                                @endcan
                                @can('backend.tag.index')
                                    <li>
                                        <a href="{{ route('backend.tag.index') }}"
                                            class="{{ Request::is('backend/tag*') ? 'active' : '' }}">{{ __('static.tag.tags') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanAny
                @endif
                @can('backend.page.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.page.index') }}"
                            class="sidebar-header {{ Request::is('backend/page*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/pages-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/pages-fill.svg') }}">
                            <span>{{ __('static.page.pages') }}</span>
                        </a>
                    </li>
                @endcan
            @endcan

            @canAny(['backend.document.index', 'backend.language.index', 'backend.payment_method.index',
                'backend.setting.index'])
                <li class="sidebar-main-title">
                    <div>
                        <h6>{{ __('static.dashboard.settings_management') }}</h6>
                    </div>
                </li>
                @can('backend.document.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/document*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/document-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/document-fill.svg') }}">
                            <span>{{ __('static.document.document') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul class="sidebar-submenu {{ Request::is('backend/document*') ? 'menu-open' : '' }}">
                            @can('backend.document.index')
                                <li>
                                    <a href="{{ route('backend.document.index') }}"
                                        class="{{ Request::is('backend/document*') && !Request::is('backend/document/create') ? 'active' : '' }}">{{ __('static.document.all') }}</a>
                                </li>
                            @endcan
                            @can('backend.document.create')
                                <li>
                                    <a href="{{ route('backend.document.create') }}"
                                        class="{{ Request::is('backend/document/create') ? 'active' : '' }}">{{ __('static.document.create') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                @canAny(['backend.theme_option.index', 'backend.home_page.index', 'backend.customization.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/home-page*') || Request::is('backend/theme-option*') || Request::is('backend/customization*') || Request::is('backend/robot*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/file-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/file-fill.svg') }}">
                            <span>{{ __('static.appearances.appearances') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/home-page*') || Request::is('backend/theme-options*') || Request::is('backend/customization*') || Request::is('backend/robot*') ? 'menu-open' : '' }}">
                            @can('backend.theme_option.index')
                                <li>
                                    <a href="{{ route('backend.theme_options.index') }}"
                                        class="{{ Request::is('backend/theme-options*') ? 'active' : '' }}">{{ __('static.theme_options.theme_options') }}</a>
                                </li>
                            @endcan
                            @can('backend.home_page.index')
                                <li>
                                    <a href="{{ route('backend.home_page.index', ['locale' => $locale]) }}"
                                        class="{{ Request::is('backend/home-page*') ? 'active' : '' }}">{{ __('static.appearances.home_page') }}</a>
                                </li>
                            @endcan
                            @can('backend.customization.index')
                                <li>
                                    <a href="{{ route('backend.customization.index') }}"
                                        class="{{ Request::is('backend/customization*') ? 'active' : '' }}">{{ __('static.appearances.customizations') }}</a>
                                </li>
                            @endcan
                            @can('backend.theme_option.index')
                                <li>
                                    <a href="{{ route('backend.robot.index') }}"
                                        class="{{ Request::is('backend/robot*') ? 'active' : '' }}">{{ __('static.appearances.robots') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanAny
                @can('backend.language.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.systemLang.index') }}"
                            class="sidebar-header {{ Request::is('backend/systemLang*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/language-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/language-fill.svg') }}">
                            <span>{{ __('static.language.languages') }}</span>
                        </a>
                    </li>
                @endcan
                @canAny(['backend.email_template.index', 'backend.sms_template.index',
                    'backend.push_notification_template.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);" class="sidebar-header {{ Request::is('backend/email-template*') || Request::is('backend/sms-template*') || Request::is('backend/push-notification-template*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/edit-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/edit-fill.svg') }}">
                            <span>{{ __('static.notify_templates.notify_templates') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/email-template') || Request::is('backend/sms-template') || Request::is('backend/push-notification-template') ? 'menu-open' : '' }}">
                            @can('backend.email_template.index')
                                <li>
                                    <a href="{{ route('backend.email-template.index') }}"
                                        class="{{ Request::is('backend/email-template') ? 'active' : '' }}">{{ __('static.email_templates.email') }}</a>
                                </li>
                            @endcan
                            @can('backend.sms_template.index')
                                <li>
                                    <a href="{{ route('backend.sms-template.index') }}"
                                        class="{{ Request::is('backend/sms-template') ? 'active' : '' }}">{{ __('static.sms_templates.sms') }}</a>
                                </li>
                            @endcan
                            @can('backend.push_notification_template.index')
                                <li>
                                    <a href="{{ route('backend.push-notification-template.index') }}"
                                        class="{{ Request::is('backend/push-notification-template') ? 'active' : '' }}">{{ __('static.push_notification_templates.push_notification') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                @can('backend.payment_method.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.paymentmethods.index') }}"
                            class="sidebar-header {{ Request::is('backend/payment-methods*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/payment-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/payment-fill.svg') }}">
                            <span>{{ __('static.payment_methods.payment_methods') }}</span>
                        </a>
                    </li>
                @endcan
                @can('backend.sms_gateway.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.smsgateways.index') }}"
                            class="sidebar-header {{ Request::is('backend/sms-gateways*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/sms-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/sms-fill.svg') }}">
                            <span>{{ __('static.sms_gateways.sms_gateways') }}</span>
                        </a>
                    </li>
                @endcan
                @can('backend.custom_sms_gateway.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.custom-sms-gateway.index') }}"
                            class="sidebar-header {{ Request::is('backend/custom-sms-gateway*') ? 'active' : '' }}">
                            <img class="inactive-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/sms-gateways-line.svg') }}">
                            <img class="active-icon"
                                src="{{ asset('admin/images/svg/sidebar-icon/sms-gateways-fill.svg') }}">
                            <span>{{ __('static.custom_sms_gateways.custom_sms_gateways') }}</span>
                        </a>
                    </li>
                @endcan

                @canAny(['backend.backup.index', 'backend.system_tool.index'])
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="javascript:void(0);"
                            class="sidebar-header {{ Request::is('backend/backup') || Request::is('backend/activity-logs') || Request::is('backend/cleanup-db') || Request::is('backend.import*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/file-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/file-fill.svg') }}">
                            <span>{{ __('static.system_tools.system_tools') }}</span>
                            <img class="stroke-icon" src="{{ asset('admin/images/svg/arrow-right-2.svg') }}">
                        </a>
                        <ul
                            class="sidebar-submenu {{ Request::is('backend/backup*') || Request::is('backend/activity-logs*') || Request::is('backend/cleanup-db*') || Request::is('backend/import*') ? 'menu-open' : '' }}">
                            @can('backend.backup.index')
                                <li>
                                    <a href="{{ route('backend.backup.index') }}"
                                        class="{{ Request::is('backend/backup*') ? 'active' : '' }}">{{ __('static.system_tools.backup') }}</a>
                                </li>
                            @endcan
                            @can('backend.system_tool.index')
                                <li>
                                    <a href="{{ route('backend.activity-logs.index') }}"
                                        class="{{ Request::is('backend/activity-logs*') ? 'active' : '' }}">{{ __('static.system_tools.activity_logs') }}</a>
                                </li>
                            @endcan
                            @can('backend.system_tool.index')
                                <li>
                                    <a href="{{ route('backend.cleanup-db.index') }}"
                                        class="{{ Request::is('backend/cleanup-db*') ? 'active' : '' }}">{{ __('static.system_tools.database_cleanup') }}</a>
                                </li>
                            @endcan
                            @can('backend.system_tool.index')
                                <li>
                                    <a href="{{ route('backend.import.index') }}"
                                        class="{{ Request::is('backend/import*') ? 'active' : '' }}">{{ __('static.system_tools.import_export') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanAny
                @can('backend.setting.index')
                    <li>
                        <i class="ri-pushpin-2-line"></i>
                        <a href="{{ route('backend.settings.index') }}"
                            class="sidebar-header {{ Request::is('backend/settings*') ? 'active' : '' }}">
                            <img class="inactive-icon" src="{{ asset('admin/images/svg/sidebar-icon/service-line.svg') }}">
                            <img class="active-icon" src="{{ asset('admin/images/svg/sidebar-icon/service-fill.svg') }}">
                            <span>{{ __('static.settings.settings') }}</span>
                        </a>
                    </li>
                @endcan
            @endcan

            {{-- <li class="fix-bottom-box">
                <ul class="fix-bottom-list">
                    <li>
                        <div class="theme-option-box">
                            <div class="dark-light-mode" id="dark-system">
                                <i data-feather="sun" class="dark-mode"></i>
                                <i data-feather="moon" class="light-mode"></i>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('frontend.home') }}" class="log-out-btn btn" target="_blank"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Browse Frontend">
                                    <i data-feather="globe"></i>
                                </a>

                                <button class="log-out-btn btn">
                                    <a href="{{ route('frontend.logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Logout">
                                        <i data-feather="log-out"></i>
                                    </a>
                                    <form action="{{ route('frontend.logout') }}" method="POST" class="d-none"
                                        id="logout-form">
                                        @csrf
                                    </form>
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </li> --}}
        </ul>

        <div class="fix-bottom-box">
            <ul class="fix-bottom-list">
                <li class="w-100">
                    <div class="theme-option-box">
                        <div class="dark-light-mode" id="dark-system">
                            <i data-feather="sun" class="dark-mode"></i>
                            <i data-feather="moon" class="light-mode"></i>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('frontend.home') }}" class="log-out-btn btn" target="_blank"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Browse Frontend">
                                <i data-feather="globe"></i>
                            </a>

                            <button class="log-out-btn btn">
                                <a href="{{ route('frontend.logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Logout">
                                    <i data-feather="log-out"></i>
                                </a>
                                <form action="{{ route('frontend.logout') }}" method="POST" class="d-none"
                                    id="logout-form">
                                    @csrf
                                </form>
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
@push('js')
<!-- Page Sidebar End -->
<script>
  $(document).ready(function() {
    "use strict";
    if ($(".sidebar .sidebar-menu") && $(".page-wrapper")) {
        $(".sidebar .sidebar-menu").animate({
                scrollTop: $(".sidebar .sidebar-menu a.active").offset().top - 400,
            },
            0
        );
    }
  })
</script>
<script>
    (function() {
        var sidebarMenu = document.querySelector(".page-sidebar .sidebar-menu");
        var pinTitle = document.querySelector(".pin-title");
        let pinIcons = document.querySelectorAll(".sidebar-menu > li .ri-pushpin-2-line");

        let originalScrollTop = sidebarMenu.scrollTop; // Store original scroll position

        function togglePinnedName() {
            if (document.querySelectorAll(".pined").length > 0) {
                pinTitle.classList.add("show");
            } else {
                pinTitle.classList.remove("show");
            }
        }

        pinIcons.forEach((item) => {
            var linkName = item.parentNode.querySelector("span").innerText;
            var InitialLocalStorage = JSON.parse(localStorage.getItem("pins") || "[]");

            // Restore pinned state from localStorage
            if (InitialLocalStorage.includes(linkName)) {
                item.parentNode.classList.add("pined");
            }

            item.addEventListener("click", (event) => {
                var localStoragePins = JSON.parse(localStorage.getItem("pins") || "[]");
                let listItem = item.parentNode; // The <li> being pinned/unpinned

                listItem.classList.toggle("pined");

                if (listItem.classList.contains("pined")) {
                    if (!localStoragePins.includes(linkName)) {
                        localStoragePins.push(linkName);
                    }

                    // Ensure original position is stored BEFORE scrolling
                    originalScrollTop = sidebarMenu.scrollTop;

                    // Move pinned item to the top
                    let scrollToPosition = listItem.getBoundingClientRect().top + sidebarMenu
                        .scrollTop - 50;
                    smoothScrollTo(sidebarMenu, scrollToPosition, 400);
                } else {
                    localStoragePins = localStoragePins.filter((pin) => pin !== linkName);
                    smoothScrollTo(sidebarMenu, originalScrollTop,
                        400); // Scroll back to original position
                }

                localStorage.setItem("pins", JSON.stringify(localStoragePins));
                togglePinnedName();
            });
        });

        function smoothScrollTo(element, target, duration) {
            var start = element.scrollTop;
            var change = target - start;
            var currentTime = 0;
            var increment = 20;

            function animateScroll() {
                currentTime += increment;
                var val = easeInOutQuad(currentTime, start, change, duration);
                element.scrollTop = val;
                if (currentTime < duration) {
                    setTimeout(animateScroll, increment);
                }
            }
            animateScroll();
        }

        function easeInOutQuad(t, b, c, d) {
            t /= d / 2;
            if (t < 1) return (c / 2) * t * t + b;
            t--;
            return (-c / 2) * (t * (t - 2) - 1) + b;
        }

        togglePinnedName();
    })();

</script>
@endpush
