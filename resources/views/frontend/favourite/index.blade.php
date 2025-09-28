@use('app\Helpers\Helpers')
@use('App\Enums\FavouriteListEnum')
@use('App\Enums\SymbolPositionEnum')
@extends('frontend.layout.master')
@php
$activeType = request()->type ?? 'provider';
@endphp
@push('css')
<!-- Flatpicker css -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush
@section('title', __('frontend::static.wishlist.wishlist'))
@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.wishlist.home')}}</a>
    <a class="breadcrumb-item"
        href="{{ route('frontend.service.index') }}">{{__('frontend::static.wishlist.services')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.wishlist.wishlist')}}</span>
</nav>
@endsection
@section('content')
<!-- Service List Section Start -->
<section class="service-list-section section-b-space">
    <div class="container-fluid-lg">
        <div class="service-list-content">
            <div class="favorite-tab">
                <h3>Wishlist</h3>
                <ul class="nav nav-tabs" id="faviconTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ ($activeType == 'provider') ? 'show active' : '' }}" id="provider-tab" data-bs-toggle="tab" data-bs-target="#provider" type="button" role="tab" aria-controls="provider" aria-selected="true">{{__('frontend::static.wishlist.provider')}}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ ($activeType == 'service') ? 'show active' : '' }}" id="service-tab" data-bs-toggle="tab" data-bs-target="#service" type="button" role="tab" aria-controls="service" aria-selected="false">{{__('frontend::static.wishlist.service')}}</button>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="faviconTabContent">
                <div class="tab-pane fade {{ ($activeType == 'provider') ? 'show active' : '' }}" id="provider"
                    role="tabpanel" aria-labelledby="provider-tab">
                    <div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1 g-sm-4 g-3">
                        @forelse ($providers as $provider)
                        <div class="col">
                            <div class="expert-content">
                                <div class="card gray-card">
                                    <div class="gray-card-img">
                                        <img src="{{ Helpers::isFileExistsFromURL($provider->provider?->media?->first()?->getUrl(), true) }}"
                                            alt="{{ $provider->provider?->name }}" class="img-fluid profile-pic">
                                        @auth
                                        <div class="like-icon active" id="favouriteDiv"
                                            data-provider-id="{{ $provider?->id }}">
                                            <img class="img-fluid icon outline-icon"
                                                src="{{ asset('frontend/images/svg/heart-outline.svg')}}"
                                                alt="whishlist">
                                            <img class="img-fluid icon fill-icon"
                                                src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                                        </div>
                                        @endauth
                                    </div>
                                    <div class="card-body">
                                        <div class="card-title">
                                            <a href="{{ route('frontend.provider.details', ['slug' => $provider->slug]) }}">
                                                <h4>{{ $provider->provider?->name }}</h4>
                                            </a>
                                            <div class="rate">
                                                <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star"
                                                    class="img-fluid star">
                                                <small>{{ $provider->provider?->review_ratings }}</small>
                                            </div>
                                        </div>
                                        <div class="location">
                                            <i class="iconsax" icon-name="location"></i>
                                            <h5>{{ $provider->provider?->primary_address?->state?->name }}
                                                -
                                                {{ $provider->provider?->primary_address?->country?->name }}
                                            </h5>
                                        </div>

                                        <div class="card-detail">
                                            <p>{{ $provider->provider?->primary_address?->address }},
                                                {{ $provider->provider?->primary_address?->postal_code }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="no-data-found">
                            <svg class="no-data-img">
                                <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                            </svg>
                            {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                            <p>{{__('frontend::static.wishlist.providers_not_found')}}</p>
                        </div>
                        @endforelse
                    </div>
                    @if(count($providers ?? []))
                    @if($providers?->lastPage() > 1)
                    <div class="row">
                        <div class="col-12">
                            <div class="pagination-main section-b-space">
                                <ul>
                                    {{-- {!! $providers->links() !!} --}}
                                    {!! $providers->appends(['type' => 'provider'])->links() !!}
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
                <div class="tab-pane fade {{ ($activeType == 'service') ? 'show active' : '' }}" id="service"
                    role="tabpanel" aria-labelledby="service-tab">
                    <div class="row row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                        @forelse ($services as $service)
                        <div class="col">
                            <div class="card">
                                <div class="discount-tag">{{ $service?->service?->discount }}%</div>
                                <div class="overflow-hidden b-r-5">
                                    <a class="card-img">
                                        <img src="{{ $service->service?->web_img_thumb_url }}" alt="feature"
                                            class="img-fluid">
                                    </a>
                                    @auth
                                    <div class="like-icon active" id="favouriteDiv"
                                        data-service-id="{{ $service?->id }}">
                                        <img class="img-fluid icon outline-icon"
                                            src="{{ asset('frontend/images/svg/heart-outline.svg')}}" alt="whishlist">
                                        <img class="img-fluid icon fill-icon"
                                            src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                                    </div>
                                    @endauth
                                </div>
                                <div class="card-body">
                                    <div class="service-title">
                                        <h4>
                                            <a href="{{ route('frontend.service.details', $service->service->slug) }}">{{ $service->service->title }}</a>
                                        </h4>
                                        <div class="d-flex align-items-center gap-1">
                                             @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service?->price) }}</del>
                                                <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service?->service_rate) }}</small>
                                            @else
                                                <del>{{ Helpers::covertDefaultExchangeRate($service->service?->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                <small>{{ Helpers::covertDefaultExchangeRate($service->service?->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="service-detail mt-1">
                                        <ul>
                                            <li class="time">
                                                <i class="iconsax" icon-name="clock"></i>
                                                <span>{{ $service->service?->duration }}
                                                    {{ $service->service?->duration_unit }}</span>
                                            </li>
                                            <li class="service">{{__('frontend::static.wishlist.min')}}
                                                {{ $service->service?->required_servicemen }}
                                                {{__('frontend::static.wishlist.servicemen_required')}}</li>
                                        </ul>
                                        <p>{{ $service->service?->description }}</p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="footer-detail">
                                        <img src="{{ Helpers::isFileExistsFromURL($service?->service?->user?->media?->first()->getURL(), true) }}" alt="feature"
                                            class="img-fluid">
                                        <div>
                                            <p>{{ $service?->service?->user?->name }}</p>
                                            <div class="rate">
                                                <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star"
                                                    class="img-fluid star">
                                                <small>{{ $service?->service?->user?->review_ratings ?? 'Unrated' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <a type="button" class="btn btn-outline d-inline-block w-auto"
                                        data-bs-toggle="modal"
                                        data-bs-target="#bookServiceModal-{{ $service->service?->id }}">
                                        {{__('frontend::static.wishlist.book_now')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="no-data-found">
                            <svg class="no-data-img">
                                <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                            </svg>
                            {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                            <p>{{__('frontend::static.wishlist.services_not_found')}}</p>
                        </div>
                        @endforelse
                    </div>
                    @if(count($services ?? []))
                    @if($services?->lastPage() > 1)
                    <div class="row">
                        <div class="col-12">
                            <div class="pagination-main section-b-space">
                                <ul>
                                    {!! $services->appends(['type' => 'service'])->links() !!}
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@forelse($services as $service)
@if ($service->service)
@includeIf('frontend.inc.modal', ['service' => $service->service])
@endif
@empty
@endforelse
<!-- Service List Section End -->
@endsection

@push('js')
<!-- Flat-picker js -->
<script src="{{ asset('frontend/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('frontend/js/flat-pickr/custom-flatpickr.js') }}"></script>

@auth
<script src="{{ asset('frontend/js/custom-wishlist.js') }}"></script>
@endauth
@endpush