@use('app\Helpers\Helpers')
@use('App\Models\Setting')
@use('App\Enums\AdvertisementTypeEnum')
@use('App\Enums\SymbolPositionEnum')
@php
    $homePage = Helpers::getCurrentHomePage();
    $settings = Setting::pluck('values')->first();
    $homePageAdvertiseBanners = Helpers::getHomePageAdvertiseBanners();
    $advertiseServices = Helpers::getHomePageAdvertiseServices();
@endphp
@extends('frontend.layout.master')

@section('title', $themeOptions['general']['site_title'])

@section('content')
    @use('App\Enums\ServiceTypeEnum')
    @use('App\Enums\FrontEnum')
    <!-- Home Banner Section Start -->
    <section class="home-section pt-0 overflow-hidden">
        <div class="home-icon">
            <svg class="image-1 lozad">
                <use xlink:href="{{ asset('frontend/images/dot.svg#dots') }}"></use>
            </svg>
            {{-- <img src="{{ asset('frontend/images/Dots-1.png') }}" class="image-1 lozad" alt=""> --}}
            <img src="{{ asset('frontend/images/Dots.png') }}" class="image-2 lozad" alt="">
            <img src="{{ asset('frontend/images/gif/arrow-gif.gif') }}" class="image-3 lozad" alt="">
        </div>
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="home-contain">
                        <h1>{{ $homePage['home_banner']['title'] }}
                            <span class="home-animation">
                                {{ $homePage['home_banner']['animate_text'] }}
                                <img class="shape lozad" src="{{ asset('frontend/images/heading-bg.png') }}" alt="shape">
                            </span>
                        </h1>
                        <p>
                            {{ $homePage['home_banner']['description'] }}
                        </p>
                        @if ($homePage['home_banner']['search_enable'])
                            <div class="home-form-group">
                                <div class="input-group">
                                    <div class="position-relative w-100">
                                        <input id="searchInput" class="form-control" type="text" name="service"
                                            placeholder="{{ __('frontend::static.home_page.search_service') }}"
                                            autocomplete="off">
                                        <i class="iconsax" icon-name="search-normal-2"></i>
                                    </div>
                                    <button id="findServiceBtn" type="button" class="btn btn-solid w-auto">
                                        <i data-feather="search" class="d-sm-none"></i>
                                        <span
                                            class="d-sm-block d-none">{{ __('frontend::static.home_page.find_service') }}</span>
                                    </button>
                                </div>
                                <div id="searchResults" class="autocomplete-results" style="display: none;"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Swiper -->
        <div class="home-slider ratio_asos_1">
            @php
                $services = Helpers::getServices($homePage['home_banner']['service_ids'] ?? [])->where('status', 1);
            @endphp
            <div class="swiper service-slider">
                @if ($homePage['home_banner']['status'])
                    @if (count($services))
                        <div class="swiper-wrapper">
                            @foreach ($services as $service)
                                <!-- Slide Start -->
                                <div class="swiper-slide">
                                    <div class="service-card">
                                        <div class="img-box">
                                            <a href="{{ route('frontend.service.details', ['slug' => $service?->slug]) }}">
                                                <img class="img-fluid bg-img lozad"
                                                    src="{{ $service?->web_img_thumb_url }}" alt="service" />
                                                <div class="service-content" title="{{ $service?->title }}">
                                                    <span>{{ $service?->title }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Slide End -->
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </section>
    <!-- Home Banner Section End -->

    <!-- Category Section Start -->
    @if ($homePage['categories_icon_list']['status'])
        <section class="category-section">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ $homePage['categories_icon_list']['title'] }}</h2>
                    <a class="view-all" href="{{ route('frontend.category.index') }}" rel="noopener noreferrer">
                        {{ __('frontend::static.home_page.browse_all_categories') }}
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                </div>
                @php
                    $categories = Helpers::getCategories($homePage['categories_icon_list']['category_ids'] ?? []);
                @endphp
                @if (count($categories))
                    <div class="swiper nav-tabs nav category-slider custom-nav-tabs" id="myTab">
                        <div class="swiper-wrapper">
                            @foreach ($categories as $category)
                                <div class="swiper-slide nav-item" id="nav-item">
                                    @php
                                        $locale = app()->getLocale();
                                        $mediaItems = $category
                                            ->getMedia('image')
                                            ->filter(function ($media) use ($locale) {
                                                return $media->getCustomProperty('language') === $locale;
                                            });
                                        $imageUrl =
                                            $mediaItems->count() > 0
                                                ? $mediaItems->first()->getUrl()
                                                : FrontEnum::getPlaceholderImageUrl();
                                    @endphp
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="{{ $category?->slug }}-tab" data-bs-toggle="tab"
                                        data-bs-target="#{{ $category?->slug }}" type="button" role="tab">
                                        <div class="img-box">
                                            <img src="{{ Helpers::isFileExistsFromURL($imageUrl, true) }}"
                                                alt="{{ $category?->title }}" class="img-fluid lozad">
                                        </div>
                                        <span>{{ $category?->title }}</span>
                                        <small>{{ count($category->services?->whereNull('parent_id')?->where('status', true)) }}
                                            {{ __('frontend::static.home_page.services') }}</small>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <ul class="nav nav-tabs custom-nav-tabs custom-scroll" id="myTab">
                        {{-- @foreach ($categories as $category)
                            <li class="nav-item" id="nav-item">
                                @php
                                    $locale = app()->getLocale();
                                    $mediaItems = $category->getMedia('image')->filter(function ($media) use ($locale) {
                                        return $media->getCustomProperty('language') === $locale;
                                    });
                                    $imageUrl =
                                        $mediaItems->count() > 0
                                            ? $mediaItems->first()->getUrl()
                                            : FrontEnum::getPlaceholderImageUrl();
                                @endphp
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $category?->slug }}-tab" data-bs-toggle="tab"
                                    data-bs-target="#{{ $category?->slug }}" type="button" role="tab">
                                    <div class="img-box">
                                        <img src="{{ Helpers::isFileExistsFromURL($imageUrl, true) }}"
                                            alt="{{ $category?->title }}" class="img-fluid lozad">
                                    </div>
                                    <span>{{ $category?->title }}</span>
                                    <small>{{ count($category->services?->whereNull('parent_id')?->where('type', ServiceTypeEnum::FIXED)) }}
                                        {{ __('frontend::static.home_page.services') }}</small>
                                </button>
                            </li>
                        @endforeach --}}
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        @foreach ($categories as $category)
                            <div class="tab-pane fade {{ $loop?->first ? 'show active' : '' }}" id="{{ $category->slug }}"
                                role="tabpanel">
                                <div class="row row-cols-2 row-cols-sm-3 ratio_94 row-cols-md-4 row-cols-xl-5 g-sm-4 g-3">
                                    @forelse ($category->services?->whereNull('parent_id')?->where('status', true)->where('is_custom_offer', false) as $services)
                                        <div class="col">
                                            <a href="{{ route('frontend.service.details', ['slug' => $services?->slug]) }}"
                                                class="category-img"><img src="{{ $services?->web_img_thumb_url }}"
                                                    alt="{{ $services?->title }}" class="bg-img lozad"></a>
                                            <a href="{{ route('frontend.service.details', ['slug' => $services?->slug]) }}"
                                                class="category-img"><span title="{{ $services?->title }}"
                                                    class="category-span">{{ $services?->title }}</span></a>
                                        </div>
                                    @empty
                                        <div class="no-data-found">
                                            <p>{{ __('frontend::static.home_page.services_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-data-found">
                        <p>{{ __('frontend::static.home_page.categories_not_found') }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif
    <!-- Category Section End -->

    <!-- Value Banner Section Start -->
    @isset($homePage['value_banners']['banners'])
        @if ($homePage['value_banners']['status'])
            <!-- Value Banner Section Start -->
            <section class="offer-section section-b-space">
                <div class="container-fluid-lg">
                    <div class="title">
                        <h2>{{ $homePage['value_banners']['title'] }}</h2>
                    </div>
                    <div class="offer-content">
                        <div class="swiper offer-slider">
                            @isset($homePage['value_banners']['banners'])
                                <div class="swiper-wrapper">
                                    @forelse ($homePage['value_banners']['banners'] as $banner)
                                        <div class="swiper-slide">
                                            <div class="position-relative">
                                                <div class="sale-tag">
                                                    <span>{{ $banner['sale_tag'] }}</span>
                                                </div>
                                                <div class="offer-img">
                                                    <img src="{{ asset($banner['image_url'] ?? 'frontend/images/img-not-found.jpg') }}"
                                                        alt="{{ $homePage['value_banners']['title'] }}" class="img-fluid lozad">
                                                </div>
                                                <div class="offer-detail">
                                                    <h3>{{ $banner['title'] }}</h3>
                                                    <p>{{ $banner['description'] }}</p>
                                                    @if ($banner['redirect_type'] == 'service-page')
                                                        <a href="{{ route('frontend.service.index') }}"
                                                            class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                    @elseif($banner['redirect_type'] == 'service-package-page')
                                                        <a href="{{ route('frontend.service-package.index') }}"
                                                            class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                    @elseif($banner['redirect_type'] == 'category-page')
                                                        <a href="{{ route('frontend.category.index') }}"
                                                            class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                    @elseif($banner['redirect_type'] == 'service')
                                                        @php
                                                            $service = Helpers::getServiceById($banner['redirect_id']);
                                                        @endphp

                                                        @if ($service && $service->slug)
                                                            <a href="{{ route('frontend.service.details', ['slug' => $service->slug]) }}"
                                                                class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                        @else
                                                            <a href="{{ route('frontend.service.index') }}"
                                                                class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                        @endif
                                                    @elseif($banner['redirect_type'] == 'package')
                                                        @php
                                                            $servicePackage = Helpers::getServicePackageById(
                                                                $banner['redirect_id'],
                                                            );

                                                        @endphp
                                                        <a href="{{ route('frontend.service-package.details', ['slug' => $servicePackage?->slug]) }}"
                                                            class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                    @elseif($banner['redirect_type'] == 'external_url')
                                                        <a href="{{ $banner['button_url'] }}"
                                                            class="btn btn-outline">{{ $banner['button_text'] }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="no-data-found">
                                            <p>{{ __('frontend::static.home_page.banners_not_found') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            @endisset
                        </div>
                    </div>
                </div>
            </section>
        @endif
    @endisset
    <!-- Value Banner Section End -->

    <!-- Service Section Start -->
    @if ($homePage['service_list_1']['status'])
        <section class="service-list-section section-bg section-b-space">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ $homePage['service_list_1']['title'] }}</h2>
                </div>
                <div class="service-list-content">
                    @php
                        $services = Helpers::getServices($homePage['service_list_1']['service_ids'] ?? [])
                            ?->paginate($themeOptions['pagination']['service_per_page'])
                            ->where('status', 1);
                    @endphp
                    @if (count($services ?? []))
                        <div class="feature-slider">
                            @foreach ($services as $service)
                                <div>
                                    <div>
                                        <div class="card">
                                            @if ($service->discount)
                                                <div class="discount-tag">{{ $service->discount }}%</div>
                                            @endif
                                            <div class="overflow-hidden b-r-5">
                                                <a href="{{ route('frontend.service.details', $service?->slug) }}"
                                                    class="card-img">
                                                    <img src="{{ $service?->web_img_thumb_url }}"
                                                        alt="{{ $service?->title }}" class="img-fluid lozad">
                                                </a>
                                            </div>
                                            <div class="card-body">
                                                <div class="service-title">
                                                    <h4>
                                                        <a title="{{ $service?->title }}"
                                                            href="{{ route('frontend.service.details', $service?->slug) }}">{{ $service?->title }}</a>
                                                    </h4>
                                                    <!---->
                                                    @if ($service->price || $service->service_rate)
                                                        <div class="d-flex align-items-center gap-1">
                                                            @if (!empty($service?->discount) && $service?->discount > 0)
                                                                <span>
                                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                        <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                                                    @else
                                                                        <del>{{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                                            {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                                    @endif
                                                                </span>
                                                                <small>
                                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                        {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}
                                                                    @else
                                                                        {{ Helpers::covertDefaultExchangeRate($service->service_rate) }}
                                                                        {{ Helpers::getDefaultCurrencySymbol() }}
                                                                    @endif
                                                                </small>
                                                            @else
                                                                <small>
                                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                        {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                                    @else
                                                                        {{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                                        {{ Helpers::getDefaultCurrencySymbol() }}
                                                                    @endif
                                                                </small>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="service-detail mt-1">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                                        <ul>
                                                            @if ($service?->duration)
                                                                <li class="time">
                                                                    <i class="iconsax" icon-name="clock"></i>
                                                                    <span>{{ $service?->duration }}{{ $service?->duration_unit === 'hours' ? 'h' : 'm' }}</span>
                                                                </li>
                                                            @endif
                                                            <li class="w-auto service-person">
                                                                <img src="{{ asset('frontend/images/svg/services-person.svg') }}"
                                                                    alt="">
                                                                <span>{{ $service->required_servicemen }}</span>
                                                            </li>
                                                        </ul>
                                                        <h6 class="service-type mt-2"><span>
                                                                {{ Helpers::formatServiceType($service?->type) }}</span>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer border-top-0">
                                                <div class="footer-detail">
                                                    <img src="{{ Helpers::isFileExistsFromURL($service?->user?->media?->first()?->getURL(), true) }}"
                                                        alt="feature" class="img-fluid lozad">
                                                    <div>
                                                        <a
                                                            href="{{ route('frontend.provider.details', ['slug' => $service?->user?->slug]) }}">
                                                            <p title=" {{ $service?->user?->name }} ">
                                                                {{ $service?->user?->name }}</p>
                                                        </a>
                                                        <div class="rate">
                                                            <img data-src="{{ asset('frontend/images/svg/star.svg') }}"
                                                                alt="star" class="img-fluid star lozad">
                                                            <small>{{ $service?->user?->review_ratings ?? 'Unrated' }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    data-bs-custom-class="book-now-tooltip"
                                                    data-bs-title="{{ __('frontend::static.home_page.book_now') }}"
                                                    type="button" class="btn book-now-btn btn-solid w-auto"
                                                    id="bookNowButton" data-bs-toggle="modal"
                                                    data-bs-target="#bookServiceModal-{{ $service->id }}"
                                                    data-login-url="{{ route('frontend.login') }}"
                                                    data-check-login-url="{{ route('frontend.check.login') }}"
                                                    data-service-id="{{ $service->id }}">
                                                    <span>{{ __('frontend::static.home_page.book_now') }}</span>
                                                    <span class="spinner-border spinner-border-sm"
                                                        style="display: none;"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="w-100 m-0">
                            <div class="no-data-found">
                                <p>{{ __('frontend::static.home_page.services_not_found') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @forelse($services as $service)
            @includeIf('frontend.inc.modal', ['service' => $service])
        @empty
        @endforelse
    @endif
    <!-- Service Section End -->

    <!-- Application Section Start -->
    @if ($homePage['download']['status'])
        <section class="application-section section-b-space overflow-hidden">
            <div class="container-fluid-lg">
                <div class="section-wrap">
                    <svg class="image-1">
                        <use xlink:href="{{ asset('frontend/images/dots.svg#dots') }}"></use>
                    </svg>
                    {{-- <img src="{{ asset('frontend/images/Dots-1.png') }}" class="image-1 lozad" alt=""> --}}
                    <img src="{{ asset('frontend/images/Dots.png') }}" class="image-2 lozad" alt="">
                    <div class="row g-5">
                        <div class="col-xl-7 col-lg-6">
                            <div class="image-grp">
                                <img src="{{ asset('frontend/images/vector.png') }}" class="vector-1 lozad"
                                    alt="app store">
                                <img src="{{ asset($homePage['download']['image_url']) }}" class="app-gif lozad"
                                    alt="app store">
                            </div>
                        </div>
                        <div class="col-xl-5 col-lg-6">
                            <div class="title">
                                <h2>{{ $homePage['download']['title'] }}</h2>
                            </div>
                            <div class="content-detail">
                                <p>
                                    {{ $homePage['download']['description'] }}
                                </p>
                                @if (!empty($homePage['download']['points']))
                                    <ul class="item-lists">
                                        @foreach ($homePage['download']['points'] as $point)
                                            <li class="item-list">{{ $point }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @isset($themeOptions['general'])
                                <div class="app-install">
                                    @isset($themeOptions['general']['app_store_url'])
                                        <a href="{{ $themeOptions['general']['app_store_url'] }}" target="_blank"
                                            rel="noopener noreferrer">
                                            <img src="{{ asset('frontend/images/app-store.png') }}" alt="app store"
                                                class="lozad">
                                        </a>
                                    @endisset
                                    @isset($themeOptions['general']['google_play_store_url'])
                                        <a href="{{ $themeOptions['general']['google_play_store_url'] }}" target="_blank"
                                            rel="noopener noreferrer">
                                            <img src="{{ asset('frontend/images/google-play.png') }}" alt="google play"
                                                class="lozad">
                                        </a>
                                    @endisset
                                </div>
                            @endisset
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Application Section End -->

    <!-- Spcial Offer In Service Section Start -->
    @if (count($advertiseServices))
        <section class="service-list-section section-bg section-b-space">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ $homePage['special_offers_section']['service_section_title'] ? $homePage['special_offers_section']['service_section_title'] : __('Today special offers') }}
                    </h2>
                </div>
                <div class="service-list-content ratio3_2">
                    <div class="feature-slider">
                        @foreach ($advertiseServices as $advertisement)
                            @foreach ($advertisement->services as $service)
                                <div>
                                    <div class="card">
                                        @if ($service->discount)
                                            <div class="discount-tag">{{ $service->discount }}%</div>
                                        @endif
                                        <div class="overflow-hidden b-r-5">
                                            <a href="{{ route('frontend.service.details', $service?->slug) }}"
                                                class="card-img">
                                                <span class="ribbon">Trending</span>
                                                <img src="{{ $service?->web_img_thumb_url }}"
                                                    alt="{{ $service?->title }}" class="img-fluid lozad">
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <div class="service-title">
                                                <h4>
                                                    <a title="{{ $service?->title }}"
                                                        href="{{ route('frontend.service.details', $service?->slug) }}">{{ $service?->title }}</a>
                                                </h4>
                                                @if ($service->price || $service->service_rate)
                                                    <div class="d-flex align-items-center gap-1">
                                                        @if (!empty($service?->discount) && $service?->discount > 0)
                                                            <span>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                                                @else
                                                                    <del>{{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                                        {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                                @endif
                                                            </span>
                                                            <small>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}
                                                                @else
                                                                    {{ Helpers::covertDefaultExchangeRate($service->service_rate) }}
                                                                    {{ Helpers::getDefaultCurrencySymbol() }}
                                                                @endif
                                                            </small>
                                                        @else
                                                            <small>
                                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                    {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                                @else
                                                                    {{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                                    {{ Helpers::getDefaultCurrencySymbol() }}
                                                                @endif
                                                            </small>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="service-detail mt-1">
                                                <div
                                                    class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                                    <ul>
                                                        @if ($service?->duration)
                                                            <li class="time">
                                                                <i class="iconsax" icon-name="clock"></i>
                                                                <span>{{ $service?->duration }}{{ $service?->duration_unit === 'hours' ? 'h' : 'm' }}</span>
                                                            </li>
                                                        @endif
                                                        <li class="w-auto service-person">
                                                            <img src="{{ asset('frontend/images/svg/services-person.svg') }}"
                                                                alt="">
                                                            <span>{{ $service->required_servicemen }}</span>
                                                        </li>
                                                    </ul>
                                                    <h6 class="service-type mt-2">
                                                        <span>{{ Helpers::formatServiceType($service?->type) }}</span>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer border-top-0">
                                            <div class="footer-detail">
                                                <img src="{{ Helpers::isFileExistsFromURL($service?->user?->media?->first()?->getURL(), true) }}"
                                                    alt="feature" class="img-fluid lozad">
                                                <div>
                                                    <a
                                                        href="{{ route('frontend.provider.details', ['slug' => $service?->user?->slug]) }}">
                                                        <p title=" {{ $service?->user?->name }} ">
                                                            {{ $service?->user?->name }}</p>
                                                    </a>
                                                    <div class="rate">
                                                        <img data-src="{{ asset('frontend/images/svg/star.svg') }}"
                                                            alt="star" class="img-fluid star lozad">
                                                        <small>{{ $service?->user?->review_ratings ?? 'Unrated' }}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <button data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                data-bs-custom-class="book-now-tooltip"
                                                data-bs-title="{{ __('frontend::static.home_page.book_now') }}"
                                                type="button" class="btn book-now-btn btn-solid w-auto"
                                                id="bookNowButton" data-bs-toggle="modal"
                                                data-bs-target="#bookServiceModal-{{ $service->id }}"
                                                data-login-url="{{ route('frontend.login') }}"
                                                data-check-login-url="{{ route('frontend.check.login') }}"
                                                data-service-id="{{ $service->id }}">
                                                <span>{{ __('frontend::static.home_page.book_now') }}</span>
                                                <span class="spinner-border spinner-border-sm"
                                                    style="display: none;"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @includeIf('frontend.inc.modal', ['service' => $service])
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Spacial Offer In Service Section End -->

    <!-- Provider Section Start -->
    @if ($homePage['providers_list']['status'])
        <section class="expert-section section-b-space">
            <div class="container-fluid-lg">
                <div class="title dark-title">
                    <h2>{{ $homePage['providers_list']['title'] ?? __('frontend::static.home_page.expert_provider_by_rating') }}
                    </h2>
                    <a class="view-all" href="{{ route('frontend.provider.index') }}" rel="noopener noreferrer">
                        {{ __('frontend::static.home_page.view_all') }}
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                </div>
                <div class="expert-content">
                    <div class="row g-lg-5 g-sm-4 g-3">
                        @php
                            $providers = Helpers::getTopProvidersByRatings(
                                $homePage['providers_list']['provider_ids'] ?? [],
                            );
                        @endphp
                        @forelse ($providers as $provider)
                            <div class="col-xxl-3 col-lg-4 col-sm-6">
                                <div class="card dark-card">
                                    <div class="dark-card-img">
                                        <img src="{{ Helpers::isFileExistsFromURL($provider?->media?->first()?->getUrl(), true) }}"
                                            alt="{{ $provider?->name }}" class="img-fluid profile-pic lozad">
                                    </div>
                                    <div class="card-body">
                                        <div class="card-title">
                                            <a href="{{ route('frontend.provider.details', $provider->slug) }}">
                                                <h4>{{ $provider?->name }}</h4>
                                            </a>
                                            <div class="rate">
                                                <img src="{{ Helpers::isFileExistsFromURL(asset('frontend/images/svg/star.svg'), true) }}"
                                                    alt="star" class="img-fluid star lozad">
                                                <small>{{ $provider?->review_ratings }}</small>
                                            </div>
                                        </div>
                                        <div class="location">
                                            <i class="iconsax" icon-name="location"></i>
                                            <h5>{{ $provider?->primary_address?->state?->name }} -
                                                {{ $provider?->primary_address?->country?->name }}</h5>
                                        </div>
                                        <div class="card-detail">
                                            <p>{{ $provider?->primary_address?->address }},{{ $provider?->primary_address?->postal_code }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="no-data-found">
                                <p>{{ __('frontend::static.home_page.providers_not_found') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Provider Section End -->

    @if (count($homePageAdvertiseBanners))
        <!-- Today Special Offers Section Start -->
        <section class="offer-section">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ $homePage['special_offers_section']['banner_section_title'] ? $homePage['special_offers_section']['banner_section_title'] : __('Today special offers') }}
                    </h2>
                </div>

                <div class="offer-banner-slider">
                    @foreach ($homePageAdvertiseBanners as $banner)
                        @if ($banner->banner_type === AdvertisementTypeEnum::IMAGE)
                            @foreach ($banner->media as $media)
                                <a href="{{ route('frontend.provider.details', $banner?->provider?->slug) }}">
                                    <div>
                                        <div class="offer-banner">
                                            <img class="img-fluid banner-img" src="{{ $media?->getUrl() }}" />
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                        @if ($banner->banner_type === AdvertisementTypeEnum::VIDEO)
                            <iframe width="560" height="315" src="{{ $banner->video_link }}"
                                title="YouTube video player" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
        <!-- Today Special Offers Section End -->
    @endif

    <!-- Service Package Section Start -->
    @if ($homePage['service_packages_list']['status'])
        <section class="service-package-section">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ $homePage['service_packages_list']['title'] }}</h2>
                    <!-- service-package -->
                    <a class="view-all" href="{{ route('frontend.service-package.index') }}"
                        rel="noopener noreferrer">{{ __('frontend::static.home_page.view_all') }}
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                </div>
                <div class="service-package-content">
                    <div class="row g-sm-4 g-3">
                        @php
                            $servicePackages = Helpers::getServicePackagesByIds(
                                $homePage['service_packages_list']['service_packages_ids'] ?? [],
                            );
                        @endphp
                        @forelse ($servicePackages as $servicePackage)
                            @php
                                $salePrice = Helpers::getServicePackageSalePrice($servicePackage?->id);
                            @endphp
                            <div class="col-xxl-3 col-lg-4 col-sm-6">
                                <a href="{{ route('frontend.service-package.details', $servicePackage['slug']) }}"
                                    class="service-bg-{{ $servicePackage?->bg_color ?? 'primary' }} service-bg d-block">
                                    <img src="{{ asset('frontend/images/svg/2.svg') }}"
                                        alt="{{ $servicePackage?->name }}" class="img-fluid service-1 lozad">
                                    <div class="service-detail">
                                        <div class="service-icon">
                                            @php
                                                $locale = app()->getLocale();
                                                $mediaItems = $servicePackage
                                                    ->getMedia('image')
                                                    ->filter(function ($media) use ($locale) {
                                                        return $media->getCustomProperty('language') === $locale;
                                                    });
                                                $imageUrl =
                                                    $mediaItems->count() > 0
                                                        ? $mediaItems->first()->getUrl()
                                                        : FrontEnum::getPlaceholderImageUrl();
                                            @endphp
                                            <img src="{{ Helpers::isFileExistsFromURL($imageUrl, true) }}"
                                                alt="{{ $servicePackage?->services?->first()?->categories?->first()?->name }}"
                                                class="img-fluid lozad">
                                        </div>
                                        <h3>{{ $servicePackage?->title }}</h3>
                                        <div class="price">
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <span class="text-white">
                                                    {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($salePrice) }}
                                                </span>
                                            @else
                                                <span class="text-white">
                                                    {{ Helpers::covertDefaultExchangeRate($salePrice) }}
                                                    {{ Helpers::getDefaultCurrencySymbol() }}
                                                </span>
                                            @endif
                                            <span>
                                                <i class="iconsax" icon-name="arrow-right"></i>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="no-data-found">
                                <div class="col-12">
                                    <p>{{ __('frontend::static.home_page.service_package_not_found') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Service Package Section End -->

    <!-- Blog Section Start -->
    @if (
        $homePage['blogs_list']['status'] &&
            isset($homePage['blogs_list']['blog_ids']) &&
            !is_null($homePage['blogs_list']['blog_ids']))
        <section class="blog-section section-b-space">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ __('frontend::static.home_page.latest_blog') }}</h2>
                    <a class="view-all" href="{{ route('frontend.blog.index') }}"
                        rel="noopener noreferrer">{{ __('frontend::static.home_page.view_all') }}
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                </div>
                <div class="blog-content ratio2_1">
                    <div class="row row-cols-xl-3 row-cols-md-2 row-cols-sm-2 row-cols-1 g-sm-4 g-3 custom-row-col">
                        @php
                            if (isset($homePage['blogs_list']['blog_ids'])) {
                                $blogs = Helpers::getBlogsByIds($homePage['blogs_list']['blog_ids'])->where(
                                    'status',
                                    1,
                                );
                            } else {
                                $blogs = [];
                            }
                        @endphp

                        @forelse ($blogs as $blog)
                            <div class="col">
                                <div class="blog-main">

                                    <div class="card">
                                        <div class="overflow-hidden b-r-5">
                                            <a href="{{ route('frontend.blog.details', $blog?->slug) }}"
                                                class="card-img">
                                                <img src="{{ $blog?->web_img_thumb_url }}" alt="{{ $blog?->title }}"
                                                    class="img-fluid lozad">
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <h4>
                                                <a href="{{ route('frontend.blog.details', $blog?->slug) }}"
                                                    title="{{ $blog?->title }}">{{ $blog?->title }}
                                                </a>
                                            </h4>
                                            <ul class="blog-detail">
                                                <li>{{ $blog?->created_at }}</li>
                                            </ul>
                                            <div class="blog-footer">
                                                <div>
                                                    <i class="iconsax" icon-name="message-dots"></i>
                                                    <span>{{ $blog?->comments_count }}</span>
                                                </div>
                                                <span>
                                                    - {{ $blog?->created_by?->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="no-data-found">
                                    <p>{{ __('frontend::static.home_page.blog_not_found') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </section>
    @endif
    <!-- Blog Section End -->

    @if ($settings['service_request']['status'] && $homePage['custom_job']['status'])
        <!-- Job Request Section Start -->
        <section class="pt-0 section-b-space">
            <div class="container-fluid-lg">
                <div class="job-request-section">
                    <div class="job-details">
                        <img src="{{ asset($homePage['custom_job']['image_url'] ?? 'frontend/images/job-request-img.png') }}"
                            class="job-img img-fluid lozad" alt="">
                        <div class="job-request-content">
                            <h3>{{ $homePage['custom_job']['title'] ?? 'Unable to find your service? you can post what need, so don\'t worry.' }}
                            </h3>
                            @auth
                                <button type="button" class="btn btn-solid w-auto" data-bs-toggle="modal"
                                    data-bs-target="#jobRequestModal">{{ $homePage['custom_job']['button_text'] ?? '+ Post New Job Request' }}</button>
                            @endauth
                            @guest
                                <a href="{{ url('login') }}"
                                    class="btn btn-solid w-auto">{{ $homePage['custom_job']['button_text'] ?? '+ Post New Job Request' }}</a>
                            @endguest
                        </div>
                    </div>
                </div>
        </section>
        <!-- Job Request Section End -->
    @endif

    <!-- Become a provider section start -->
    @if ($homePage['become_a_provider']['status'])
        <!-- Home Service Provider Section Start -->
        <section class="service-provider-section section-b-space">
            <div class="container-fluid-lg">
                <div class="section-wrap">
                    <img src="{{ asset('frontend/images/Dots-1.png') }}" class="image-1 lozad" alt="">
                    <img src="{{ asset('frontend/images/Dots.png') }}" class="image-2 lozad" alt="">
                    <div class="row g-lg-5 g-3">
                        <div class="col-xl-5 col-lg-6">
                            <div class="title">
                                <h2>{{ $homePage['become_a_provider']['title'] }}</h2>
                            </div>
                            <div class="content-detail">
                                <p>
                                    {{ $homePage['become_a_provider']['description'] }}
                                </p>
                                @if (!empty($homePage['become_a_provider']['points']))
                                    <ul class="item-lists">
                                        @forelse ($homePage['become_a_provider']['points'] as $point)
                                            <li class="item-list"> <i class="iconsax" icon-name="arrow-right"></i>
                                                {{ $point }}
                                            </li>
                                        @empty
                                        @endforelse
                                    </ul>
                                @endif
                            </div>
                            <a href="{{ route('become-provider.index') }}"
                                class="btn btn-solid">{{ $homePage['become_a_provider']['button_text'] }}
                                <i class="iconsax" icon-name="arrow-circle-right"></i>
                            </a>
                        </div>
                        <div class="col-xl-7 col-lg-6">
                            <div class="image-grp">
                                <img src="{{ asset($homePage['become_a_provider']['image_url']) }}"
                                    class="girl-img lozad" alt="app store">
                                <img src="{{ asset($homePage['become_a_provider']['float_image_1_url']) }}"
                                    class="chart-img lozad" alt="app store">
                                <img src="{{ asset($homePage['become_a_provider']['float_image_2_url']) }}"
                                    class="group-img lozad" alt="app store">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Home Service Provider Section End -->
    @endif
    <!-- Become a provider section end -->

    <!-- About Us Section Start -->
    @if ($homePage['testimonial']['status'])
        <section class="about-us-section">
            <div class="container-fluid-lg">
                <div class="title-1">
                    <h2>{{ $homePage['testimonial']['title'] }}</h2>
                </div>
                <div class="about-us-content content-t-space">
                    {{-- <img src="{{ asset('frontend/images/Dots-1.png') }}" class="image-1 lozad" alt=""> --}}
                    <svg class="image-1 lozad" data-loaded="true">
                        <use xlink:href="{{ asset('frontend/images/dot.svg#dots') }}"></use>
                    </svg>
                    <div class="swiper about-us-slider">
                        <div class="swiper-wrapper">
                            @php
                                $testimonials = Helpers::getTestimonials();
                            @endphp
                            @forelse ($testimonials as $testimonial)
                                <div class="swiper-slide">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-title">
                                                <img src="{{ Helpers::isFileExistsFromURL($testimonial?->media?->first()?->original_url, true) }}"
                                                    alt="feature" class="img-fluid lozad">
                                                <img src="{{ asset('frontend/images/svg/quote.svg') }}" alt="quote"
                                                    class="img-fluid quote lozad">
                                                {{-- <img src="{{ asset('frontend/images/svg/quote-active.svg') }}"
                                                    alt="quote" class="img-fluid quote-active lozad"> --}}

                                                <svg class="quote-active lozad">
                                                    <use
                                                        xlink:href="{{ asset('frontend/images/svg/quote-active.svg#quote-active') }}">
                                                    </use>
                                                </svg>
                                                <div>
                                                    <h3>{{ $testimonial?->name }}</h3>
                                                    <div class="rate">
                                                        <img src="{{ asset('frontend/images/svg/star.svg') }}"
                                                            alt="star" class="img-fluid star">
                                                        <small>{{ $testimonial?->rating }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-detail">
                                                <p>{{ $testimonial?->description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="no-data-found">
                                    <p>{{ __('frontend::static.home_page.testimonials_not_found') }}
                                    </p>
                                </div>
                            @endforelse
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- About Us Section End -->

    <!-- Newsletter Section Start -->
    @if ($homePage['news_letter']['status'])
        <section class="newsletter-section section-b-space">
            <div class="container-fluid-lg">
                <div class="newsletter-content">
                    {{-- <svg class="newsletter-bg">
                        <use xlink:href="{{ asset('frontend/images/newsletter.svg#newsletter-bg') }}"></use>
                    </svg> --}}
                    <div class="row g-sm-5 g-3">
                        <div class="newsletter-icons col-lg-5 col-4 text-center">
                            <img src="{{ asset('frontend/images/dots-white.png') }}" class="newsletter-1 lozad"
                                alt="">
                            <img src="{{ asset('frontend/images/dots-1-white.png') }}" class="newsletter-2 lozad"
                                alt="">
                            <img src="{{ asset('frontend/images/dots-white.png') }}" class="newsletter-3 lozad"
                                alt="">
                            <img src="{{ asset($homePage['news_letter']['bg_image_url'] ?? 'frontend/images/man.png') }}"
                                class="img-fluid man-image lozad" alt="">
                        </div>
                        <div class="col-lg-7 col-md-8 col-12">
                            <div class="newsletter-detail">
                                <h2>{{ $homePage['news_letter']['title'] }}</h2>
                                <p>{{ $homePage['news_letter']['sub_title'] }}</p>
                                <form action="{{ route('frontend.subscribe') }}" method="POST">
                                    <div class="form-group">
                                        <input class="form-control" type="email" required="" name="newsletter"
                                            placeholder="{{ __('frontend::static.home_page.enter_your_email') }}">
                                        <button type="submit"
                                            class="btn btn-dark-solid">{{ $homePage['news_letter']['button_text'] }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Newsletter Section End -->

    @if ($settings['service_request']['status'] && $homePage['custom_job']['status'])
        <!-- job request modal Section start -->
        <div class="modal fade job-request-modal" id="jobRequestModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Custom job Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('frontend.custom-job.store') }}" method="POST" class="job-request-form"
                        id="customJobForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title"
                                            class="form-check-label">{{ __('frontend::static.home_page.title') }}</label>
                                        <div class="position-relative">
                                            <i class="iconsax" icon-name="subtitles"></i>
                                            <input type="text" class="form-control form-control-white" name="title"
                                                id="title"
                                                placeholder="{{ __('frontend::static.home_page.enter_title') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment"
                                            class="form-check-label">{{ __('frontend::static.home_page.images') }}</label>
                                        <div class="position-relative">
                                            <i class="iconsax" icon-name="import-2"></i>
                                            <input type="file" class="form-control form-control-white" name="images[]"
                                                multiple id="images[]" placeholder="Click to upload">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration"
                                            class="form-check-label">{{ __('frontend::static.home_page.duration') }}</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="w-100 position-relative">
                                                <i class="iconsax" icon-name="hourglass"></i>
                                                <input type="number" class="form-control form-control-white"
                                                    name="duration" id="duration"
                                                    placeholder="{{ __('frontend::static.home_page.enter_duration') }}"
                                                    time-input>
                                            </div>
                                            <select class="form-select form-select-sm w-auto" name="duration_unit">
                                                @foreach (['hours' => 'Hours', 'minutes' => 'Minutes'] as $key => $option)
                                                    <option class="option" value="{{ $key }}"
                                                        @if (old('duration_unit', $Request->duration_unit ?? '') === $key) selected @endif>
                                                        {{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="servicemen"
                                            class="form-check-label">{{ __('frontend::static.home_page.required_servicemen') }}</label>
                                        <div class="position-relative">
                                            <i class="iconsax" icon-name="tag-user"></i>
                                            <input type="number" class="form-control form-control-white"
                                                name="required_servicemen" min="1" id="required_servicemen"
                                                placeholder="{{ __('frontend::static.home_page.enter_required_serviceman') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title"
                                            class="form-check-label">{{ __('frontend::static.home_page.price') }}</label>
                                        <div class="position-relative">
                                            <i class="iconsax" icon-name="dollar-circle"></i>
                                            <input type="number" class="form-control form-control-white" id="price"
                                                name="price"
                                                placeholder="{{ __('frontend::static.home_page.enter_price') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-12"
                                            for="category_ids">{{ __('frontend::static.home_page.categories') }}</label>
                                        <div class="col-12 error-div">
                                            <select class="form-control select-2 form-select-sm w-100" id="category_ids"
                                                name="category_ids[]" multiple
                                                data-placeholder="{{ __('static.service.select_categories') }}">
                                                <option></option>
                                                @foreach ($serviceCategories as $key => $value)
                                                    <option value="{{ $key }}"
                                                        @if (isset($default_categories) && in_array($key, $default_categories)) selected
                                                        @elseif (old('category_ids') && in_array($key, old('category_ids'))) selected @endif>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="servicemen"
                                            class="form-check-label">{{ __('frontend::static.home_page.description') }}</label>
                                        <div class="position-relative">
                                            <i class="iconsax" icon-name="clipboard-text-1"></i>
                                            <textarea rows="3" maxlength="100" class="form-control form-control-white" id="description"
                                                name="description" placeholder="{{ __('frontend::static.home_page.enter_description') }}"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer p-0">
                            <button type="submit"
                                class="btn btn-solid mt-4 mx-auto">{{ __('frontend::static.home_page.post') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- job request modal Section End -->
    @endif

@endsection

@push('js')
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        $(".select-2").select2();
        var categorySlider = new Swiper(".category-slider", {
            slidesPerView: 9,
            spaceBetween: 24,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 2,
                    spaceBetween: 15,
                },
                370: {
                    slidesPerView: 3,
                    spaceBetween: 15,
                },
                590: {
                    slidesPerView: 4,
                    spaceBetween: 15,
                },
                790: {
                    slidesPerView: 5,
                    spaceBetween: 15,
                },
                950: {
                    slidesPerView: 6,
                    //   spaceBetween: 40,
                },
                1160: {
                    slidesPerView: 7,
                    //   spaceBetween: 40,
                },
                1380: {
                    slidesPerView: 8,
                    //   spaceBetween: 40,
                },
                1600: {
                    slidesPerView: 9,
                },
            },
        });
        (function($) {
            "use strict";
            // $(document).ready(function() {

            // });
            // Ensure DOM is fully loaded before executing
            $(document).ready(function() {




                // Debounce function
                const debounce = (func, delay) => {
                    let timeout;
                    return function(...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), delay);
                    };
                };

                // Function to fetch services
                const fetchServices = debounce(function(query) {
                    $.get('{{ route('frontend.service.search') }}', {
                            term: query
                        },
                        function(data) {
                            const resultsContainer = $('#searchResults');
                            resultsContainer.empty(); // Clear previous results

                            if (data.length) {
                                const fragment = document.createDocumentFragment();
                                data.forEach(service => {
                                    const div = document.createElement('div');
                                    div.className = 'autocomplete-item';
                                    div.setAttribute('data-slug', service.slug);
                                    div.innerHTML =
                                        `<img src="${service.image}" alt="${service.title}" class="service-image"><h5>${service.title}</h5>`;
                                    fragment.appendChild(div);
                                });
                                resultsContainer.append(fragment).show();
                            } else {
                                // Show "no results" message
                                const noResultDiv = document.createElement('div');
                                noResultDiv.className = 'autocomplete-item no-result';
                                noResultDiv.innerHTML = `<h5>No result found</h5>`;
                                resultsContainer.append(noResultDiv).show();
                            }
                        });
                }, 300);

                // Search input event listener
                $('#searchInput').on('keyup', function() {
                    const query = $(this).val();
                    if (query.length > 1) fetchServices(query); // Call debounced fetch function
                    else $('#searchResults').hide(); // Hide results if no query
                });

                // Hide search results when clicking outside the input or results container
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#searchInput, #searchResults').length) {
                        $('#searchResults').hide(); // Hide results if clicking outside
                    }
                });

                // Redirect to the service page when an autocomplete item is clicked
                $(document).on('click', '.autocomplete-item', function() {
                    window.location.href = baseUrl + 'service/' + $(this).data('slug');
                });

                // Handle the "Find Service" button click event
                $('#findServiceBtn').on('click', function() {
                    let searchTerm = $('#searchInput').val().trim();
                    if (searchTerm) {
                        window.location.href = '{{ route('frontend.service.index') }}?search=' +
                            encodeURIComponent(searchTerm);
                    }
                });
            }); // End of $(document).ready()

            $('#customJobForm').validate({
                ignore: [],
                rules: {
                    title: {
                        required: true
                    },
                    'images[]': {
                        required: true,
                        extension: "jpg|jpeg|png|gif|webp"
                    },
                    duration: {
                        required: true
                    },
                    required_servicemen: {
                        required: true
                    },
                    price: {
                        required: true,
                    },
                    'category_ids[]': {
                        required: true
                    },
                },
            });
        })(jQuery);
    </script>
    <script>
        "use strict";
        $(function() {
            $(document).on('click', '.qtyadd', function () {            
                let parent = $(this).closest('.form-check');
                let input = parent.find('.additional_services_qty');
                let priceSpan = parent.find('.additional-price');
                let basePrice = parseFloat(priceSpan.data('base-price'));
                let val = +input.val();
                
                updatePrice(priceSpan, basePrice, val);
            });

            $(document).on('click', '.qtyminus', function () {
                let parent = $(this).closest('.form-check');
                let input = parent.find('.additional_services_qty');
                let priceSpan = parent.find('.additional-price');
                let basePrice = parseFloat(priceSpan.data('base-price'));
                let val = +input.val();

                updatePrice(priceSpan, basePrice, val);
            });

            // Function to update total price inside span
            function updatePrice(priceSpan, basePrice, qty) {
                let currency = "{{ Helpers::getDefaultCurrencySymbol() }}";
                let position = "{{ Helpers::getDefaultCurrency()->symbol_position->value }}";
                let total = (basePrice * qty).toFixed(2);
                
                if (position === "left") {
                    priceSpan.text(currency + total);
                } else {
                    priceSpan.text(total + " " + currency);
                }
            }

        });
    </script>
@endpush
