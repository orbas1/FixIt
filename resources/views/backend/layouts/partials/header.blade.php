@use('App\Models\Setting')
@use('App\Models\User')
@use('App\Helpers\Helpers')
@php
    $notifications = [];
    if (Auth::check()) {
        $user = User::findOrFail(Auth::user()->id);
        $notifications = $user->notifications()->latest('created_at');
    }
    $settings = Setting::first()->values;
@endphp
<!-- Page Header Start-->

{{-- <div class="page-main-header"> --}}

    @if(Request::is('backend/booking/create'))
    <div class="page-main-header open">
    @else
    <div class="page-main-header">
    @endif
    <div class="main-header-right row">
        {{-- <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group">
                <input type="text" class="form-control search-input  w-100" aria-describedby="searchHelp"
                    placeholder="Search Here...">
                <i data-feather="search" class="search-icon"></i>
                <i data-feather="x" class="close-search"></i>
            </div>
        </form> --}}
        <div class="d-flex align-items-center w-auto gap-sm-3 gap-2 p-0">
            <div class="mobile-sidebar w-auto d-lg-none">
                <div class="media-body text-end switch-sm">
                    <label class="switch h-auto">
                        <span class="cursor-pointer">
                            <i data-feather="menu" id="sidebar-toggle"></i>
                        </span>
                    </label>
                </div>
            </div>
            <a href="{{ route('backend.dashboard') }}" class="d-lg-none d-flex mobile-logo">
                <img class="blur-up lazyloaded img-fluid dark-logo"
                    src="{{ asset($settings['general']['light_logo']) ?? asset('admin/images/logo-dark.png') }}"
                    alt="site-logo">
                <img class="blur-up lazyloaded img-fluid light-logo"
                    src="{{ asset($settings['general']['light_logo']) ?? asset('admin/images/Logo-Light.png') }}"
                    alt="site-logo">
            </a>
            <button class="btn form-control search-input-btn d-lg-block d-none" data-bs-toggle="modal"
                data-bs-target="#searchModal"><i data-feather="search"></i>
                {{ __('static.search_here') }}
                <span>CTRL + M</span>
            </button>
            {{-- <form class="search-form d-lg-flex d-none">
                <input type="text" class="form-control search-input" value="" id="menu-item-search"
                    placeholder="{{ __('static.search_here') }}">
                <i data-feather="search"></i>
                <ul id="search-results" class="search-list custom-scroll d-none"></ul>
            </form> --}}
        </div>

        <div class="nav-right col">
            <ul class="nav-menus">
                <li class="onhover-dropdown quick-onhover-dropdown">
                    <div class="quick-dropdown-box">
                        <a href="{{ route('backend.booking.create') }}" class="new-btn">
                            <span class="d-xl-block d-lg-none d-md-block d-none">{{ __('static.POS') }}</span>
                            <i class="ri-add-line add d-xl-block d-lg-none d-md-block d-none"></i>
                            <i class="ri-shopping-cart-line d-xl-none d-lg-block d-md-none"></i>
                        </a>
                    </div>
                </li>
                <li class="onhover-dropdown quick-onhover-dropdown">
                    <div class="quick-dropdown-box">
                        <div class="new-btn">
                            <span class="d-xl-block d-lg-none d-md-block d-none">{{ __('static.quick_links') }}</span>
                            <i class="ri-add-line add d-xl-block d-lg-none d-md-block d-none"></i>
                            <i class="ri-links-line d-xl-none d-lg-block d-md-none"></i>
                        </div>
                        <div class="onhover-show-div">
                            <ul class="h-custom-scrollbar dropdown-list">
                                <li>
                                    <a href="{{ route('backend.booking.index') }}">
                                        <div class="svg-box">
                                            <i class="ri-book-line"></i>
                                        </div>
                                        <span>{{ __('static.bookings') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.service.index') }}">
                                        <div class="svg-box">
                                            <i class="ri-customer-service-line"></i>
                                        </div>
                                        <span>{{ __('static.service.services') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.zone.create') }}">
                                        <div class="svg-box">
                                            <i class="ri-map-pin-line"></i>
                                        </div>
                                        <span>{{ __('static.zone.create') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.provider.create') }}">
                                        <div class="svg-box">
                                            <i class="ri-user-add-line"></i>
                                        </div>
                                        <span>{{ __('static.provider.add') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.serviceman-location.index') }}">
                                        <div class="svg-box">
                                            <i class="ri-user-line"></i>
                                        </div>
                                        <span>{{ __('static.serviceman.serviceman_location') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.review.index') }}">
                                        <div class="svg-box">
                                            <i class="ri-star-line"></i>
                                        </div>
                                        <span>{{ __('static.review.review') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.theme_options.index') }}">
                                        <div class="svg-box">
                                            <i class="ri-brush-4-line"></i>
                                        </div>
                                        <span>{{ __('static.theme_options.theme_options') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.settings.index') }}">
                                        <div class="svg-box">
                                            <i class="ri-settings-3-line"></i>
                                        </div>
                                        <span>{{ __('static.settings.settings') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>

                <li class="d-lg-none d-sm-inline-block d-none header-search cursor-pointer" data-bs-toggle="modal"
                    data-bs-target="#searchModal">
                    <i data-feather="search" class="light-mode"></i>
                </li>

                <li class="cache-button d-sm-block d-none">
                    <a href="{{ route('backend.clear-cache') }}">
                        <i class="ri-brush-line"></i>
                    </a>
                </li>

                {{-- <li class="cache-button">
                    <a href="{{ route('frontend.home') }}" target="_blank">
                        <i class="ri-global-line"></i>
                    </a>
                </li> --}}

                <li class="onhover-dropdown">
                    <a class="txt-dark" href="javascript:void(0)">
                        <h6>{{ strtoupper(Session::get('locale', Helpers::getDefaultLanguageLocale())) }}</h6>
                    </a>
                    <ul class="language-dropdown onhover-show-div p-20  language-dropdown-hover">
                        @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                            <li>
                                <a href="{{ route('lang', @$lang?->locale) }}" data-lng="{{ @$lang?->locale }}"><img
                                        class="active-icon"
                                        src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"><span>{{ @$lang?->name }}
                                        ({{ @$lang?->locale }})
                                    </span></a>
                            </li>
                        @empty
                            <li>
                                <a href="{{ route('lang', Helpers::getDefaultLanguageLocale()) }}" data-lng="en"><img class="active-icon"
                                        src="{{ asset('admin/images/flags/LR.png') }}"><a href="javascript:void(0)"
                                        data-lng="en">English</a>
                            </li>
                        @endforelse
                    </ul>
                </li>
                {{-- <li class="dark-light-mode" id="dark-mode">
                    <i data-feather="moon" class="light-mode"></i>
                    <i data-feather="sun" class="dark-mode"></i>
                </li> --}}
                <li class="onhover-dropdown">
                    <i data-feather="bell"></i>
                    <span
                        class="badge badge-pill badge-primary pull-right notification-badge">{{ count(auth()->user()->unreadNotifications) }}</span>
                    </span>
                    <ul class="notification-dropdown onhover-show-div">
                        <li>
                            <h4>{{ __('static.contact_mails') }}
                                <span
                                    class="badge badge-pill badge-primary pull-right">{{ count(auth()->user()->unreadNotifications) }}</span>
                            </h4>
                        </li>
                        @forelse (auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                            <li>
                                <i data-feather="disc"></i>
                                <p>{{ $notification->data['message'] ?? '' }}</p>
                            </li>
                        @empty
                            <div class="d-flex flex-column no-data-detail">
                                <img class="mx-auto d-flex" src="{{ asset('admin/images/svg/no-data.svg') }}"
                                    alt="no-image">
                                <div class="data-not-found">
                                    <span>{{ __('static.data_not_found') }}</span>
                                </div>
                            </div>
                        @endforelse
                        <li>
                            <a href="{{ route('backend.list-notification') }}" class="btn btn-primary">View All</a>
                        </li>
                    </ul>
                </li>
                <li class="onhover-dropdown">
                    <a href="{{ route('backend.account.profile') }}" class="media profile-box">
                        @if (Auth::user()->getFirstMediaUrl('image'))
                            <img class="align-self-center profile-image pull-right img-fluid rounded-circle blur-up lazyloaded"
                                src="{{ Auth::user()->getFirstMediaUrl('image') }}" alt="header-user">
                        @else
                            <div class="initial-letter">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        @endif
                        <span class="d-md-flex d-none">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="profile-dropdown onhover-show-div p-20 profile-dropdown-hover">
                        <li><a href="{{ route('backend.account.profile') }}">
                                <i data-feather="user"></i><span>{{ __('static.edit_profile') }}</span></a></li>
                        <li>
                            <a href="{{ route('frontend.logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i data-feather="log-out"></i><span>{{ __('static.logout') }}</span>
                            </a>
                            <form action="{{ route('frontend.logout') }}" method="POST" class="d-none"
                                id="logout-form">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- Page Header Ends -->

<!-- Search modal start -->

<div class="modal fade search-modal" id="searchModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Search box</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="from-group">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Enter your search"
                            id="menu-item-search" autofocus>
                        <i class="ri-search-line"></i>
                    </div>
                </div>

                <div class="search-suggestion-box">
                    <div class="search-input-box" id="recent-search">
                        <h6>Recent Searches</h6>
                        <div class="search-list" id="recent-searches">
                            <h4>No recent searches</h4>
                        </div>
                    </div>

                    <div class="search-input-box d-none" id="search-result">
                        <h6>Search Results</h6>
                        <ul class="search-list d-none" id="search-results"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('js')
    <script>
        $(document).on('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && (String.fromCharCode(e.which).toLowerCase() === 'm')) {
                $("#searchModal").modal('show');
            }
        });

        function saveRecentSearch(text, url) {
            if (!text || !url) return;

            let recentSearches = JSON.parse(localStorage.getItem('recentSearches')) || [];

            recentSearches = recentSearches.filter(item => item.url !== url);

            recentSearches.unshift({
                text,
                url
            });

            if (recentSearches.length > 5) recentSearches.pop();

            localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
            displayRecentSearches();
        }

        function displayRecentSearches() {
            let recentSearches = JSON.parse(localStorage.getItem('recentSearches')) || [];
            let container = document.getElementById('recent-searches');

            container.innerHTML = '';
            if (recentSearches.length === 0) {
                container.innerHTML = '<h4>No recent searches</h4>';
                return;
            }

            recentSearches.forEach(search => {
                let li = document.createElement('li');
                li.innerHTML = `<a href="${search.url}"><i class="ri-history-line"></i> ${search.text}</a>`;
                container.appendChild(li);
            });
        }

         function menuItemSearch() {
            var input = document.getElementById('menu-item-search');
            var filter = input.value.toUpperCase();
            var ul = document.getElementById("sidebar-menu");
            var li = ul.getElementsByTagName('li');
            var resultsContainer = document.getElementById("search-results");
            $("#recent-search").removeClass("d-none");

            if (filter !== '') {
                $("#recent-search").addClass("d-none");
                $("#search-result").removeClass("d-none");
                $("#search-results").removeClass("d-none").addClass("d-block"); // Updated to use d-block for consistent display

                resultsContainer.innerHTML = '';
                var hasMatches = false;

                for (var i = 0; i < li.length; i++) {
                    var a = li[i].getElementsByTagName("a")[0];
                    if (a) {
                        var txtValue = a.textContent || a.innerText;
                        var href = a.getAttribute('href');

                        if (href !== "javascript:void(0);" && txtValue.toUpperCase().indexOf(filter) > -1) {
                            var highlightedText = txtValue.replace(new RegExp(`(${filter})`, "gi"), "<b>$1</b>");
                            var clone = document.createElement("li");
                            clone.innerHTML =
                                `<a href="${href}" onclick="saveRecentSearch('${txtValue}', '${href}')"><i class="ri-article-line"></i> ${highlightedText}</a>`;

                            resultsContainer.appendChild(clone);
                            hasMatches = true;
                        }
                    }
                }

                if (!hasMatches) {
                    resultsContainer.innerHTML = '<li class="no-data">No result found</li>';
                    $("#recent-search").addClass("d-none");
                    $("#search-results").removeClass("d-none").addClass("d-block");
                    $("#search-result").removeClass("d-none");
                }
            } else {
                $("#search-results").removeClass("d-flex").addClass("d-none");
                $("#search-result").addClass("d-none");
            }
        }

        (function($) {
            "use strict";
            document.getElementById('menu-item-search').addEventListener('keyup', menuItemSearch);
            displayRecentSearches();
        })(jQuery);

        $('#searchModal').on('shown.bs.modal', function() {
            $(this).find('[autofocus]').focus();
        });
    </script>
@endpush
