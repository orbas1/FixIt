
@use('app\Helpers\Helpers')
@use('App\Enums\FavouriteListEnum')
@use('App\Enums\BookingEnumSlug')
@use('App\Enums\FrontEnum')
@use('App\Enums\SymbolPositionEnum')
@use('App\Models\Booking')
@php
    $locale =  app()->getLocale();
    $mediaItems = $service?->getMedia('image')?->filter(function ($media) use ($locale) {
        return $media->getCustomProperty('language') === $locale;
    });
    $imageUrl = $mediaItems?->count() > 0  ? $mediaItems?->first()?->getUrl() : FrontEnum::getPlaceholderImageUrl();
@endphp
@extends('frontend.layout.master')

@section('title', $service?->title)
@section('meta_description', $service?->meta_description ?? $service?->description)
@section('og_title', $service?->meta_title ?? $service?->title)
@section('og_description', $service?->meta_description ?? $service?->description)
@section('og_image', $imageUrl)
@section('twitter_title', $service?->meta_title ?? $service?->title)
@section('twitter_description', $service?->meta_description ?? $service?->description)
@section('twitter_image', $imageUrl)

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{ __('frontend::static.services.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.service.index') }}">{{ __('frontend::static.services.services')}}</a>
    <span class="breadcrumb-item active">{{ $service?->title }}</span>
</nav>
@endsection


@section('content')
<!-- Service List Section Start -->
<section class="service-list-section">
    <div class="container-fluid-lg">
        <div class="row service-list-content g-sm-4 g-3">
            <div class="col-xxl-8 col-lg-7">
                <div class="swiper service-detail-slider">
                    <div class="swiper-wrapper">
                        @foreach ($service->web_img_galleries_url as $imageUrl)
                        <div class="swiper-slide ratio_45">
                            <div class="position-relative">
                                <div class="service-img">
                                    @php
                                        $consumerId = auth()->id();
                                        $favouriteServiceId = \App\Models\FavouriteList::where('consumer_id', $consumerId)->pluck('service_id')->toArray();
                                    @endphp
                                        <img src="{{ $imageUrl}}" alt="offer" class="bg-img">
                                    @auth
                                    <div class="like-icon b-top" id="favouriteDiv" data-service-id="{{ $service?->id }}">
                                        <img class="img-fluid icon outline-icon " src="{{ asset('frontend/images/svg/heart-outline.svg')}}" alt="whishlist">
                                        <img class="img-fluid icon fill-icon" src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                                    </div>
                                    @endauth
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next5"></div>
                    <div class="swiper-button-prev5"></div>
                    <div class="swiper-pagination"></div>
                </div>
                <div class="detail-content service-details-content">
                    <div class="title">
                        <h3>{{ $service->title }}</h3>
                        @if ($service->discount)
                            <span class="badge danger-light-badge">
                                {{ $service->discount }}% {{ __('frontend::static.services.discount')}}
                            </span>
                        @endif
                    </div>
                    <p>
                        {{ $service->description }}
                    </p>
                    <div>
                        {!! $service->content !!}
                    </div>
                </div>
            </div>

            @auth
            @includeIf('frontend.inc.modal',['service' => $service])
            @endauth

            <div class="col-xxl-4 col-lg-5">
                <div class="sticky">
                    <div class="amount">
                        <div class="amount-header">
                            <span>{{ __('frontend::static.services.amount')}} :</span>
                            <small class="value">
                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                    {{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}
                                @else
                                    {{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                @endif
                            </small>
                        </div>
                        <div class="amount-detail">
                            <ul>
                                @if($service?->duration)
                                <li>
                                    <i class="iconsax" icon-name="clock"></i>
                                    {{ __('frontend::static.services.around')}} {{ $service?->duration }} {{ $service?->duration_unit }}
                                </li>
                                @endif
                                <li>
                                    <i class="iconsax" icon-name="user-1-tag"></i>
                                    {{ __('frontend::static.services.min')}} {{ $service?->required_servicemen }} {{ __('frontend::static.services.servicemen_required_for')}}
                                </li>
                                <li>
                                    <i class="iconsax" icon-name="text"></i>
                                    {{ __('frontend::static.bookings.service_type')}} :  {{ Helpers::formatServiceType($service?->type) }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn book-now-btn btn-solid mt-sm-4 mt-3" data-bs-toggle="modal" data-bs-toggle="modal" 
                            data-bs-target="#bookServiceModal-{{$service->id}}"
                            data-login-url="{{ route('frontend.login') }}"
                            data-check-login-url="{{ route('frontend.check.login') }}"
                            data-service-id="{{ $service->id }}">
                    {{ __('frontend::static.services.book_now')}}<span class="spinner-border spinner-border-sm" style="display: none;"></span>
                    </button>

                    <div class="provider-detail mt-sm-4 mt-3">
                        <label class="mb-sm-3 mb-2">{{ __('frontend::static.services.provider_details')}}</label>
                        <div class="provider-content">
                            <div class="profile-bg"></div>
                            <div class="profile">
                                <a href="{{route('frontend.provider.details', ['slug' => $service?->user?->slug])}}"> 
                                    <img src="{{ $service?->user?->media?->first()?->original_url ?? asset('frontend/images/user.png') }}" alt="{{ $service?->user->name }}" class="img">
                                </a>
                                <a href="{{route('frontend.provider.details', ['slug' => $service?->user?->slug])}}"> 
                                <h3 class="mt-sm-2 mt-1">{{ $service?->user->name }}</h3>
                                </a>
                            </div>
                            <div class="profile-detail">
                                <ul>
                                    @if ($service?->user->known_languages && count($service?->user->known_languages))
                                    <li>
                                        <label for="language">{{ __('frontend::static.services.known_language')}}</label>
                                        <span>{{ implode($service?->user->known_languages) }}</span>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="success-light-badge badge">
                                <span>{{ $service?->user->served }} {{ __('frontend::static.services.service_delivered')}}</span>
                            </div>
                            @if($service?->user?->experience_duration)
                            <div class="danger-light-badge badge mb-0">
                                <span>{{$service?->user?->experience_duration}} {{$service?->user?->experience_interval}} {{ __('frontend::static.services.of_experience')}}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 content-b-space">
                <div class="title">
                    <h2>{{ __('frontend::static.services.featured_services')}}</h2>
                    <a class="view-all" href="{{route('frontend.service.index')}}">
                    {{ __('frontend::static.services.view_all')}}
                        <i class="iconsax" icon-name="arrow-right"></i>
                    </a>
                </div>
                <div class="row g-sm-4 g-3">
                    @foreach ($recentService as $service)
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="card">
                            @if($service->discount)
                            <div class="discount-tag">{{ $service->discount }}%</div>
                            @endif
                            @auth
                            <div class="like-icon" id="favouriteDiv" data-service-id="{{ $service?->id }}">
                                <img class="img-fluid icon outline-icon" src="{{ asset('frontend/images/svg/heart-outline.svg')}}"
                                    alt="whishlist">
                                <img class="img-fluid icon fill-icon" src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                            </div>
                            @endauth
                            <div class="overflow-hidden b-r-5">
                                <a href="{{ route('frontend.service.details', $service?->slug) }}" class="card-img">
                                    <img src="{{ $service?->web_img_thumb_url }}" alt="{{ $service?->title }}" class="img-fluid">
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="service-title">
                                    @if($service?->title)
                                    <h4><a href="{{ route('frontend.service.details', $service?->slug) }}">{{ $service?->title }}</a>
                                    </h4>
                                    @endif
                                    @if($service->price || $service->service_rate)
                                    <div class="d-flex align-items-center gap-1">
                                        @if(!empty($service?->discount) && $service?->discount > 0)
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                                <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                            @else
                                                <del>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                            @endif
                                        @else
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</small>
                                            @else
                                                <small>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                            @endif
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <div class="service-detail mt-1">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <ul>
                                            @if ($service?->duration)
                                                <li class="time">
                                                    <i class="iconsax" icon-name="clock"></i>
                                                    <span>{{ $service?->duration }}{{ $service?->duration_unit === 'hours' ? 'h' : 'm' }}</span>
                                                </li>
                                            @endif
                                            <li class="w-auto service-person">
                                                <img src="{{ asset('frontend/images/svg/services-person.svg') }}" alt="">
                                                <span>{{ $service?->required_servicemen }}</span>
                                            </li>
                                        </ul>
                                        <h6 class="service-type mt-2"><span>{{ Helpers::formatServiceType($service?->type) }}</span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer border-top-0">
                                <div class="footer-detail">
                                    <img src="{{ $service?->user?->media?->first()?->getURL() }}" alt="feature" class="img-fluid">
                                    <div>
                                        <p>{{ $service?->user?->name }}</p>
                                        <div class="rate">
                                            <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star" class="img-fluid star">
                                            <small>{{ $service?->user?->review_ratings ?? 'Unrated' }}</small>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn book-now-btn btn-solid w-auto" id="bookNowButton"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#bookServiceModal-{{ $service->id }}"
                                        data-login-url="{{ route('frontend.login') }}"
                                        data-check-login-url="{{ route('frontend.check.login') }}"
                                        data-service-id="{{ $service->id }}">
                                    {{ __('frontend::static.services.book_now') }}
                                    <span class="spinner-border spinner-border-sm" style="display: none;"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Service List Section End -->
@forelse($recentService as $service)
@includeIf('frontend.inc.modal',['service' => $service])
@empty
@endforelse
@endsection

@push('js')
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
@auth
<script src="{{ asset('frontend/js/custom-wishlist.js') }}"></script>
@endauth
@endpush