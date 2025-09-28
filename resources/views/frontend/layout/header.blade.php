@use('app\Helpers\Helpers')

<!-- ICONSAX css-->
<link rel="stylesheet" rel="preload" type="text/css" href="{{ asset('frontend/css/vendors/iconsax/iconsax.css') }}">
<!-- Header Section Start -->
<header>
    <div class="sub-header d-xxl-none">
        <div class="container-fluid-xl">
            <ul class="nav-right">
                @php
                    $currency = session('currency', Helpers::getDefaultCurrencyCode());
                    $defaultCurrency = Helpers::getCurrencyByCode($currency);
                    $defaultCurrencyImg = $defaultCurrency?->media?->first()?->getUrl();
                    $currencies = Helpers::getActiveCurrencies() ?? [];
                @endphp

                @if (count($currencies) <= 1)
                    <li class="no-dropdown nav-item-right">
                        <a href="{{ route('set.currency', $defaultCurrency?->code) }}" class="currency-btn">
                            <span>
                                @if (Helpers::isFileExistsFromURL($defaultCurrencyImg))
                                    <img src="{{ Helpers::isFileExistsFromURL($defaultCurrencyImg, true) }}"
                                        alt="" class="img-fluid">
                                @else
                                    <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                        class="img-fluid">
                                @endif
                                {{ $defaultCurrency?->code }}
                            </span>
                        </a>
                    </li>
                @else
                    <li class="dropdown currency-dropdown nav-item-right">
                        <button id="unitSelected" class="currency-btn">
                            <span>
                                @if (Helpers::isFileExistsFromURL($defaultCurrencyImg))
                                    <img src="{{ Helpers::isFileExistsFromURL($defaultCurrencyImg, true) }}"
                                        alt="" class="img-fluid">
                                @else
                                    <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                        class="img-fluid">
                                @endif
                                {{ $defaultCurrency?->code }}
                            </span>
                            <i class="iconsax" icon-name="chevron-down"></i>
                        </button>

                        <ul class="onhover-show-div">
                            @forelse($currencies as $currency)
                                @php
                                    $currencyImg = $currency?->media?->first()?->getUrl();
                                @endphp
                                <li class="currency">
                                    <a href="{{ route('set.currency', $currency?->code) }}">
                                        @if (Helpers::isFileExistsFromURL($currencyImg))
                                            <img class="img-fluid"
                                                src="{{ Helpers::isFileExistsFromURL($currencyImg, true) }}"
                                                alt="{{ $currency?->code }}" />
                                        @else
                                            <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                                class="img-fluid">
                                        @endif
                                        {{ @$currency?->code }}
                                    </a>
                                </li>
                            @empty
                                <li class="currency">
                                    <img src="{{ $defaultCurrency?->media?->first()?->getUrl() ?? asset('admin/images/No-image-found.jpg') }}"
                                        alt="{{ $defaultCurrency?->code }}"
                                        class="img-fluid">{{ $defaultCurrency?->code }}
                                </li>
                            @endforelse
                        </ul>
                    </li>
                @endif
                @php
                    $lang = Helpers::getLanguageByLocale(Session::get('locale', Helpers::getDefaultLanguageLocale()));
                    $flag = $lang?->flag;
                @endphp
                @if (count(Helpers::getLanguages()) <= 1)
                    <li class="no-dropdown nav-item-right">
                        <a href="{{ route('lang', ['locale' => @$lang?->locale, 'locale_flag' => @$lang?->flag_path]) }}"
                            class="language-btn">
                            <span>
                                @if (Helpers::isFileExistsFromURL($flag))
                                    <img src="{{ Helpers::isFileExistsFromURL($flag, true) }}"
                                        alt="{{ $lang?->name }}" class="img-fluid">
                                @else
                                    <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                        class="img-fluid">
                                @endif
                                {{ strtoupper(Session::get('locale', Helpers::getDefaultLanguageLocale())) }}
                            </span>
                        </a>
                    </li>
                @else
                    <li class="dropdown language-dropdown nav-item-right">
                        <button id="languageSelected" class="language-btn">
                            <span>
                                @if (Helpers::isFileExistsFromURL($flag))
                                    <img src="{{ Helpers::isFileExistsFromURL($flag, true) }}"
                                        alt="{{ $lang?->name }}" class="img-fluid">
                                @else
                                    <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                        class="img-fluid">
                                @endif
                                {{ strtoupper(Session::get('locale', Helpers::getDefaultLanguageLocale())) }}
                            </span>
                            <i class="iconsax" icon-name="chevron-down"></i>
                        </button>
                        <ul class="onhover-show-div">
                            @forelse (Helpers::getLanguages() as $lang)
                                <li class="lang">
                                    <a href="{{ route('lang', ['locale' => @$lang?->locale, 'locale_flag' => @$lang?->flag_path]) }}"
                                        data-lng="{{ @$lang?->locale }}">
                                        <img class="img-fluid lang-img" alt="{{ $lang?->name }}"
                                            src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}">
                                        {{ @$lang?->name }}
                                    </a>
                                </li>
                            @empty
                                <li class="lang">
                                    <a href="{{ route('lang', 'en') }}" data-lng="en">
                                        <img src="{{ asset('admin/images/flags/LR.png') }}" alt="en">
                                        En
                                    </a>
                                </li>
                            @endforelse
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
    <div class="top-header">
        <div class="container-fluid-xl">
            <div class="row">
                <div class="col-12">
                    <nav class="navbar custom-navbar navbar-expand-xl navbar-light justify-xl-content-start">
                        <div class="logo-content">
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <a class="navbar-brand" href="{{ url('/') }}">
                                @isset($themeOptions['general']['header_logo'])
                                    <img src="{{ asset($themeOptions['general']['header_logo']) }}" alt="">
                                @endisset
                            </a>
                            @if (Helpers::isZoneExists())
                                <div class="dropdown location-dropdown d-md-flex">
                                    <button id="add-btn" class="location-btn">
                                        <i class="iconsax" icon-name="location"></i>
                                        <span class="location-part" id="location">
                                            <span class="location-place"></span>
                                        </span>
                                        <i class="iconsax arrow" icon-name="chevron-down"></i>
                                    </button>
                                    <div id="overlay" class="overlay" style="display: none;"></div>
                                    <div id="locationBox" class="onhover-show-div">
                                        <div class="detect-location">
                                            <div class="detect-location-title">
                                                <i class="iconsax location-icon" icon-name="location"></i>
                                                <h4>{{ __('frontend::static.location.title') }}</h4>
                                                @if (count(session('zoneIds', [])))
                                                    <a class="close-btn" id="locationCloseBtn"><i
                                                            class="iconsax location-icon"
                                                            icon-name="close-circle"></i></a>
                                                @endif
                                            </div>
                                            <div class="location-content">
                                                <button class="btn btn-outline detect-btn" id="useCurrentLocationBtn"
                                                    type="button">
                                                    <i class="iconsax" icon-name="gps"></i>
                                                    {{ __('frontend::static.location.use_current_location') }} <span
                                                        class="spinner-border spinner-border-sm"
                                                        style="display: none;"></span></button>
                                                <span class="or-text">{{ __('frontend::static.location.or') }}</span>
                                                <button type="button" data-bs-target="#locationSelected"
                                                    id="selectManuallyBtn" class="btn btn-solid manually-location-btn"
                                                    data-bs-toggle="modal">
                                                    {{ __('frontend::static.location.select_manually') }} </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <div class="navbar-header d-xl-none d-flex">
                                <h4>Menu</h4>
                                <button class="btn-close" type="button"></button>
                            </div>
                            <ul class="navbar-nav mx-auto custom-scroll">
                                @if ($themeOptions['header']['home'] ?? false)
                                    <li class="nav-item">
                                        <a class="nav-link @if (Request::is('/')) active @endif"
                                            href="{{ url('/') }}">{{ __('frontend::static.header.home') }}</a>
                                    </li>
                                @endif
                                @if ($themeOptions['header']['categories'] ?? false)
                                    <li class="nav-item">
                                        <a class="nav-link @if (Request::is('category')) active @endif"
                                            href="{{ route('frontend.category.index') }}">{{ __('frontend::static.header.category') }}</a>
                                    </li>
                                @endif
                                @if ($themeOptions['header']['services'] ?? false)
                                    <li class="nav-item">
                                        <a class="nav-link @if (Request::is('service')) active @endif"
                                            href="{{ route('frontend.service.index') }}">{{ __('frontend::static.header.service') }}</a>
                                    </li>
                                @endif
                                @if ($themeOptions['header']['service_packages'] ?? false)
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link @if (Request::is('service-package')) active @endif"
                                        href="{{ route('frontend.service-package.index') }}">{{ __('frontend::static.header.packages') }}</a>
                                </li>
                                @auth
                                    @if ($themeOptions['header']['booking'] ?? false)
                                        <li class="nav-item">
                                            <a class="nav-link @if (Request::is('booking')) active @endif"
                                                href="{{ route('frontend.booking.index') }}">{{ __('frontend::static.header.booking') }}</a>
                                        </li>
                                    @endif
                                @endauth
                                @if ($themeOptions['header']['blogs'] ?? false)
                                    <li class="nav-item">
                                        <a class="nav-link @if (Request::is('blog')) active @endif"
                                            href="{{ route('frontend.blog.index') }}">{{ __('frontend::static.header.blog') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <ul class="nav-right">
                            {{-- <li class="icon nav-item-right d-md-none d-inline-block">
                                <i class="iconsax" icon-name="search-normal-2"></i>
                            </li> --}}
                            <a href="{{ route('frontend.cart.index') }}" class="icon nav-item-right">
                                <i class="iconsax" icon-name="shopping-cart"></i>
                                @if (count(session('cartItems') ?? []))
                                    <span class="badge badge-danger">{{ count(session('cartItems') ?? []) }}</span>
                                @endif
                            </a>

                            @php
                                $currency = session('currency', Helpers::getDefaultCurrencyCode());
                                $defaultCurrency = Helpers::getCurrencyByCode($currency);
                                $defaultCurrencyImg = $defaultCurrency?->media?->first()?->getUrl();
                                $currencies = Helpers::getActiveCurrencies() ?? [];
                            @endphp

                            @if (count($currencies) <= 1)
                                <li class="no-dropdown nav-item-right d-xxl-flex d-none">
                                    <a href="{{ route('set.currency', $defaultCurrency?->code) }}"
                                        class="currency-btn">
                                        <span>
                                            @if (Helpers::isFileExistsFromURL($defaultCurrencyImg))
                                                <img src="{{ Helpers::isFileExistsFromURL($defaultCurrencyImg, true) }}"
                                                    alt="" class="img-fluid">
                                            @else
                                                <img src="{{ asset('admin/images/No-image-found.jpg') }}"
                                                    alt="" class="img-fluid">
                                            @endif
                                            {{ $defaultCurrency?->code }}
                                        </span>
                                    </a>
                                </li>
                            @else
                                <li class="dropdown currency-dropdown nav-item-right d-xxl-flex d-none">
                                    <button id="unitSelected" class="currency-btn">
                                        <span>
                                            @if (Helpers::isFileExistsFromURL($defaultCurrencyImg))
                                                <img src="{{ Helpers::isFileExistsFromURL($defaultCurrencyImg, true) }}"
                                                    alt="" class="img-fluid">
                                            @else
                                                <img src="{{ asset('admin/images/No-image-found.jpg') }}"
                                                    alt="" class="img-fluid">
                                            @endif
                                            {{ $defaultCurrency?->code }}
                                        </span>
                                        <i class="iconsax" icon-name="chevron-down"></i>
                                    </button>

                                    <ul class="onhover-show-div">
                                        @forelse($currencies as $currency)
                                            @php
                                                $currencyImg = $currency?->media?->first()?->getUrl();
                                            @endphp
                                            <li class="currency">
                                                <a href="{{ route('set.currency', $currency?->code) }}">
                                                    @if (Helpers::isFileExistsFromURL($currencyImg))
                                                        <img class="img-fluid"
                                                            src="{{ Helpers::isFileExistsFromURL($currencyImg, true) }}"
                                                            alt="{{ $currency?->code }}" />
                                                    @else
                                                        <img src="{{ asset('admin/images/No-image-found.jpg') }}"
                                                            alt="" class="img-fluid">
                                                    @endif
                                                    {{ @$currency?->code }}
                                                </a>
                                            </li>
                                        @empty
                                            <li class="currency">
                                                <img src="{{ $defaultCurrency?->media?->first()?->getUrl() ?? asset('admin/images/No-image-found.jpg') }}"
                                                    alt="{{ $defaultCurrency?->code }}"
                                                    class="img-fluid">{{ $defaultCurrency?->code }}
                                            </li>
                                        @endforelse
                                    </ul>
                                </li>
                            @endif

                            @php
                                $lang = Helpers::getLanguageByLocale(
                                    Session::get('locale', Helpers::getDefaultLanguageLocale()),
                                );
                                $flag = $lang?->flag;
                            @endphp
                            @if (count(Helpers::getLanguages()) <= 1)
                                <li class="no-dropdown nav-item-right d-xxl-flex d-none">
                                    <a href="{{ route('lang', ['locale' => @$lang?->locale, 'locale_flag' => @$lang?->flag_path]) }}"
                                        class="language-btn">
                                        <span>
                                            @if (Helpers::isFileExistsFromURL($flag))
                                                <img src="{{ Helpers::isFileExistsFromURL($flag, true) }}"
                                                    alt="{{ $lang?->name }}" class="img-fluid">
                                            @else
                                                <img src="{{ asset('admin/images/No-image-found.jpg') }}"
                                                    alt="" class="img-fluid">
                                            @endif
                                            {{ strtoupper(Session::get('locale', Helpers::getDefaultLanguageLocale())) }}
                                        </span>
                                    </a>
                                </li>
                            @else
                                <li class="dropdown language-dropdown nav-item-right d-xxl-flex d-none">
                                    <button id="languageSelected" class="language-btn">
                                        <span>
                                            @if (Helpers::isFileExistsFromURL($flag))
                                                <img src="{{ Helpers::isFileExistsFromURL($flag, true) }}"
                                                    alt="{{ $lang?->name }}" class="img-fluid">
                                            @else
                                                <img src="{{ asset('admin/images/No-image-found.jpg') }}"
                                                    alt="" class="img-fluid">
                                            @endif
                                            {{ strtoupper(Session::get('locale', Helpers::getDefaultLanguageLocale())) }}
                                        </span>
                                        <i class="iconsax" icon-name="chevron-down"></i>
                                    </button>
                                    <ul class="onhover-show-div">
                                        @forelse (Helpers::getLanguages() as $lang)
                                            <li class="lang">
                                                <a href="{{ route('lang', ['locale' => @$lang?->locale, 'locale_flag' => @$lang?->flag_path]) }}"
                                                    data-lng="{{ @$lang?->locale }}">
                                                    <img class="img-fluid lang-img" alt="{{ $lang?->name }}"
                                                        src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}">
                                                    {{ @$lang?->name }}
                                                </a>
                                            </li>
                                        @empty
                                            <li class="lang">
                                                <a href="{{ route('lang', 'en') }}" data-lng="en">
                                                    <img src="{{ asset('admin/images/flags/LR.png') }}"
                                                        alt="en">
                                                    En
                                                </a>
                                            </li>
                                        @endforelse
                                    </ul>
                                </li>
                            @endif
                            @auth
                                <li class="dropdown profile-dropdown nav-item-right">
                                    <button id="profileSelected" class="profile-btn">
                                        <span>
                                            @php
                                                $profileImg = auth()?->user()?->getFirstMediaUrl('image');
                                            @endphp
                                            @if (Helpers::isFileExistsFromURL($profileImg))
                                                <img src="{{ $profileImg }}" alt="" class="img-fluid">
                                            @else
                                                <span
                                                    class="profile-name initial-letter">{{ substr(auth()->user()?->name, 0, 1) }}</span>
                                            @endif
                                            <span class="profile-text">
                                                {{ auth()?->user()?->name }}
                                            </span>

                                        </span>
                                        <i class="iconsax d-sm-block d-none" icon-name="chevron-down"></i>
                                    </button>
                                    <ul class="onhover-show-div profile-onhover">
                                        <li class="profile">
                                            <a href="{{ route('frontend.account.profile.index') }}">
                                                <i class="iconsax"
                                                    icon-name="user-1-square"></i>{{ __('frontend::static.header.my_account') }}
                                            </a>
                                        </li>
                                        <li class="profile">
                                            <a href="{{ route('frontend.logout') }}"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="iconsax"
                                                    icon-name="logout-1"></i>{{ __('frontend::static.header.logout') }}
                                            </a>
                                            <form action="{{ route('frontend.logout') }}" method="POST" class="d-none"
                                                id="logout-form">
                                                @csrf
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            @else
                                <li class="login-btn nav-item-right">
                                    <a href="{{ url('login') }}"
                                        class="btn btn-outline">{{ __('frontend::static.header.login') }}
                                        {{ __('frontend::static.header.now') }}</a>
                                </li>
                            @endauth
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header Section End -->

<!-- location detected modal start -->
<div class="modal fade location-detected-modal" id="locationSelected" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">
                    {{ __('frontend::static.home_page.your_location') }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input class="form-control location-search" type="text" id="locationSearchInput" name="location"
                    placeholder="{{ __('frontend::static.home_page.search_location') }}">
                <!-- Spinner HTML -->
                <div class="position-relative">
                    <div id="locationSpinner" class="location-loader" style="display: none;">
                        <div class="spinner-border"></div>
                    </div>

                    <ul id="location-list" class="location-list"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- location detected modal end -->

<!-- Iconsax js -->
<script src="{{ asset('frontend/js/iconsax/iconsax.js') }}"></script>

@push('js')
    <script>
        (function($) {
            $('.currency').on('click', function() {
                let selectedCurrency = $(this).text().trim();
                $.ajax({
                    url: "{{ url('set-currency') }}/" + selectedCurrency,
                    type: "GET",
                    success: function() {
                        $('#unitSelected span').html($(this)
                            .html()); // Update selected currency button
                        location.reload(); // Reload the page to reflect the currency change
                    },
                    error: function(xhr) {
                        console.error("Error setting currency:", xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush
