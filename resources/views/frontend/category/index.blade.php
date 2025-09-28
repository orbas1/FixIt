@use('app\Helpers\Helpers')
@use('App\Enums\AdvertisementTypeEnum')
@use('App\Enums\ServiceTypeEnum')
@use('App\Enums\SymbolPositionEnum')

@php    
    $homePage = Helpers::getCurrentHomePage();
    $categories = $categories->paginate($themeOptions['pagination']['categories_per_page'] ?? null);
    $categoryPageAdvertiseBanners = Helpers::getCategoryPageAdvertiseBanners();
    $advertiseServices = Helpers::getCategoryPageAdvertiseServices();
@endphp
@extends('frontend.layout.master')

@section('title', __('frontend::static.categories.categories'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{__('frontend::static.categories.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.categories.categories')}}</span>
</nav>
@endsection
@section('content')
<section class=" pt-0 content-b-space">

    <!-- Today Special Offers Section Start -->
    @if (count($categoryPageAdvertiseBanners))    
    <section class="offer-section section-b-space">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>{{  $homePage['special_offers_section']['banner_section_title'] ? $homePage['special_offers_section']['banner_section_title'] : __('Today special offers') }}</h2>
            </div>

            <div class="offer-banner-slider">
                @foreach ($categoryPageAdvertiseBanners as $banner)
                    @if ($banner->banner_type === AdvertisementTypeEnum::IMAGE)
                        @foreach ($banner->media as $media)
                            <a href="{{ route('frontend.provider.details', $banner?->provider?->slug) }}">
                                <div>
                                    <div class="offer-banner">
                                        <img class="img-fluid banner-img" src="{{ Helpers::isFileExistsFromURL($media?->getUrl(), true)  }}" />
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                    @if ($banner->banner_type === AdvertisementTypeEnum::VIDEO)
                        <iframe width="560" height="315" src="{{ $banner->video_link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif
    <!-- Today Special Offers Section End -->

    <!-- Special Offer In Service Section Start -->
    @if (count($advertiseServices))
    <section class="service-list-section section-bg section-b-space">
        <div class="container-fluid-lg">
            <div class="title">
                <h2>{{  $homePage['special_offers_section']['service_section_title'] ? $homePage['special_offers_section']['service_section_title'] : __('Today special offers') }}</h2>
            </div>
            <div class="service-list-content">
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
                                                                <del>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                            @endif                                                        
                                                        </span>
                                                        <small>
                                                             @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                                {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}
                                                            @else
                                                                {{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                            @endif
                                                        </small>
                                                    @else
                                                        <small>
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}
                                                        @else
                                                            {{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                        @endif</small>
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
                                                <h6 class="service-type  mt-2"><span>{{ Helpers::formatServiceType($service?->type) }}</span>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer border-top-0">
                                        <div class="footer-detail">
                                            <img src="{{ Helpers::isFileExistsFromURL($service?->user?->media?->first()?->getURL(), true) }}"
                                                alt="feature" class="img-fluid lozad">
                                            <div>
                                                <a href="{{ route('frontend.provider.details', ['slug' => $service?->user?->slug]) }}">
                                                    <p title=" {{ $service?->user?->name }} "> {{ $service?->user?->name }}</p>
                                                </a>
                                                <div class="rate">
                                                    <img data-src="{{ asset('frontend/images/svg/star.svg') }}"
                                                        alt="star" class="img-fluid star lozad">
                                                    <small>{{ $service?->user?->review_ratings ?? 'Unrated' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn book-now-btn btn-solid w-auto"
                                            id="bookNowButton" data-bs-toggle="modal"
                                            data-bs-target="#bookServiceModal-{{ $service->id }}"
                                            data-login-url="{{ route('frontend.login') }}"
                                            data-check-login-url="{{ route('frontend.check.login') }}"
                                            data-service-id="{{ $service->id }}">
                                            {{ __('frontend::static.home_page.book_now') }}
                                            <span class="spinner-border spinner-border-sm"
                                                style="display: none;"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
    <!-- Special Offer In Service Section End -->
    

    @forelse ($categories as $category)
    {{-- @if (count($category->services)) --}}
    <!-- Category Section Start -->
    <section class="salon-section content-t-space2">
        <div class="container-fluid-lg">
            <div class="accordion categories-accordion" id="salon-{{ $category->id }}">
                <div class="accordion-item">
                    <div class="accordion-header" id="salonItem">
                        <div class="title w-100" data-bs-toggle="collapse"
                                data-bs-target="#collapseSalon-{{ $category->id }}" aria-expanded="true"
                                aria-controls="collapseSalon">
                            <h2 title="">{{ $category?->title }}</h2>
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSalon-{{ $category->id }}" aria-expanded="true"
                                aria-controls="collapseSalon">
                            </button>
                        </div>
                    </div>
                    <div id="collapseSalon-{{ $category->id }}" class="accordion-collapse collapse show"
                        aria-labelledby="collapseSalon" data-bs-parent="#salon-{{ $category->id }}">
                        <div class="accordion-body">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-sm-4 g-3 ratio_94">
                                @forelse ($category->services?->whereNull('parent_id')?->where('status', true) as $service)
                                    <div class="col">
                                        <a href="{{route('frontend.service.details', ['slug' => $service?->slug])}}"
                                        class="category-img"><img src="{{ $service?->web_img_thumb_url }}"
                                        alt="{{ $service?->title }}" class="bg-img lozad"></a>
                                        <a href="{{route('frontend.service.details', ['slug' => $service?->slug])}}"
                                            class="category-img"><span title="{{ $service?->title }}" class="category-span">{{ $service?->title }}</span></a>
                                    </div>
                                @empty
                                    <div class="no-data-found">
                                        <p>{{__('frontend::static.categories.services_not_found')}}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Category Section End -->
    {{-- @endif --}}
    @empty
    <div class="no-data-found category-no-data">
        <svg class="no-data-img">
            <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
        </svg>
        {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
        <p>{{__('frontend::static.categories.categories_not_found')}}</p>
    </div>
    @endforelse
    @if(count($categories ?? []))
        @if($categories?->lastPage() > 1)
            <div class="row">
                <div class="col-12">
                    <div class="pagination-main">
                        <ul class="pagination">
                            {!! $categories->links() !!}
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    @endif
</section>
@foreach ($advertiseServices as $advertisement)
    @foreach ($advertisement->services as $service)
        @includeIf('frontend.inc.modal', ['service' => $service])
    @endforeach
@endforeach
@endsection
