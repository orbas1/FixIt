@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')

@extends('frontend.layout.master')

@section('title', $provider?->name)

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{__('frontend::static.providers.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.provider.index')}}">{{__('frontend::static.providers.providers')}}</a>
    <span class="breadcrumb-item active">{{ $provider->name }}</span>
</nav>
@endsection

@section('content')

<!-- Service List Section Start -->
<section class="section-b-space service-list-section">
    <div class="container-fluid-lg">
        <div class="row g-4">
            <div class="col-custom-3">
                <div class="filter sticky">
                    <div class="provider-card">
                         <button class="close filter-close d-xl-none">
                            <i class="iconsax" icon-name="add"></i>
                        </button>
                        <div class="provider-detail">
                            @auth
                            <div class="like-icon" id="favouriteDiv" data-provider-id="{{ $provider?->id }}">
                                <img class="img-fluid icon outline-icon" src="{{ asset('frontend/images/svg/heart-outline.svg')}}"
                                    alt="whishlist">
                                <img class="img-fluid icon fill-icon" src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                            </div>
                            @endauth
                            <div class="provider-content">
                                <div class="profile-bg"></div>
                                <div class="profile">
                                    @php
                                    $profileImg = $provider?->media?->first()?->getUrl();
                                    @endphp
                                    @if(Helpers::isFileExistsFromURL($profileImg))
                                    <img src="{{ $profileImg }}" alt="{{ $provider?->name }}" class="img-fluid provider-profile-img">
                                    @else
                                    <span class="profile-name initial-letter">{{ substr($provider?->name, 0, 1) }}</span>
                                    @endif

                                    <div class="d-flex align-content-center gap-2 mt-2">
                                        <h3>{{ $provider?->name }}</h3>
                                        <div class="rate">
                                            <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star"
                                                class="img-fluid star">
                                            <small>{{ $provider?->review_ratings }}</small>
                                        </div>
                                    </div>
                                    <div class="location mt-2">
                                        <i class="iconsax" icon-name="location"></i>
                                        <h5>{{ $provider?->primary_address?->state?->name }}-{{ $provider?->primary_address?->country?->name }}
                                        </h5>
                                    </div>
                                    <div class="delivered">
                                        <span>Services delivered :</span>
                                        <small>{{ $provider->served }}</small>
                                    </div>
                                </div>
                                <label class="mt-3 mb-2">Details of Provider</label>
                                <div class="profile-info">
                                    {{-- <div class="mb-3">
                                        <label>
                                            <i class="iconsax" icon-name="mail"></i>
                                            Mail
                                        </label>
                                        @if($provider?->email)
                                        <p>{{ $provider?->email }}</p>
                                        @else
                                        <p>Email Not Found</p>
                                        @endif
                                    </div>
                                    <div class="mb-3">
                                        <label>
                                            <i class="iconsax" icon-name="phone"></i>
                                            Call
                                        </label>
                                        @if($provider?->code && $provider?->phone)
                                        <p>+{{ $provider?->code }} {{ $provider?->phone }}</p>
                                        @else
                                        <p>NUmber Not Found</p>
                                        @endif
                                    </div> --}}
                                    @if (count($provider->knownLanguages))
                                    <div>
                                        <label>
                                            <i class="iconsax" icon-name="globe"></i>
                                            Known languages
                                        </label>
                                        <div class="d-flex align-content-center gap-3 mt-2">
                                            @foreach($provider?->knownLanguages as $knownLanguages)
                                            <button class="btn btn-solid-gray">{{$knownLanguages?->key}}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @php
                                $providerReviews = $provider?->provider_rating_list ?? [];
                                $percentages = Helpers::getRatingPercentages($providerReviews, $provider?->reviews_count);
                                @endphp
                                <div class="rating-bars mt-4">
                                    @forelse($percentages as $index => $percentage)
                                    <div class="rating-bar">
                                        <div class="left">{{ 5 - $index }} {{__('frontend::static.providers.star')}}</div>
                                        <div class="progress bar">
                                            <div class="progress-bar bar-item" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="right">{{ round($percentage,2) }}%</div>
                                    </div>
                                    @empty
                                    <p>{{__('frontend::static.providers.reviews_not_found')}}</p>
                                    @endforelse
                                </div>

                                <div class="provider-detail-tab mt-4">
                                    <ul class="nav nav-tabs" id="providerDetailTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="provider-service-tab"
                                                data-bs-toggle="tab" data-bs-target="#provider-service" type="button"
                                                role="tab" aria-controls="provider-service"
                                                aria-selected="false">{{__('frontend::static.providers.services')}}</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="provider-review-tab" data-bs-toggle="tab"
                                                data-bs-target="#provider-review" type="button" role="tab"
                                                aria-controls="provider-review" aria-selected="true">{{__('frontend::static.providers.reviews')}}</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-custom-9 no-data-col-custom-9">
                <button class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">Provider Details</button>
                <div class="tab-content m-0" id="providerDetailTabContent">
                    <div class="tab-pane fade show active" id="provider-service" role="tabpanel"
                        aria-labelledby="provider-service-tab">
                        <div class="row ratio3_2 row-cols-sm-1 row-cols-lg-2 row-cols-xl-3 g-4">
                            @forelse($services ?? [] as $service)
                            <div class="col">
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
                                        <div class="card-img">
                                            <a href="{{route('frontend.service.details', $service?->slug)}}"
                                                class="card-img">
                                                <img src="{{ $service?->web_img_thumb_url }}"
                                                    alt="{{ $service?->title }}" class="bg-img">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="service-title">
                                            <h4>
                                                <a
                                                    href="{{ route('frontend.service.details', $service?->slug) }}">{{ $service?->title }}</a>
                                            </h4>
                                            <div class="d-flex align-items-center gap-1">
                                                @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                    <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                                    <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                                @else
                                                    <del>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                    <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="service-detail mt-1">
                                            <ul>
                                                <li class="time">
                                                    <i class="iconsax" icon-name="clock"></i>
                                                    <span>{{ $service?->duration }}
                                                        {{ $service?->duration_unit }}</span>
                                                </li>
                                                <li class="w-auto service-person">
                                                    <img src="{{ asset('frontend/images/svg/services-person.svg') }}"
                                                        alt="">
                                                    <span>{{ $service?->required_servicemen }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn  book-now-btn btn-outline mt-2"  data-bs-toggle="modal" data-bs-target="#bookServiceModal-{{ $service->id }}" data-login-url="{{ route('frontend.login') }}"
                                            data-check-login-url="{{ route('frontend.check.login') }}"
                                            id="bookNowButton">
                                            {{__('frontend::static.providers.book_now')}}
                                            <span class="spinner-border spinner-border-sm" style="display: none;"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="no-data-found">
                                <img class="img-fluid no-data-img" src="{{ asset('admin/images/svg/no-data.svg') }}" alt="">    
                                <p>{{__('frontend::static.providers.service_not_found')}}</p>
                            </div>
                            @endforelse
                        </div>

                        @if(count($services ?? []))
                        @if($services?->lastPage() > 1)
                        <div class="col-12">
                            <div class="pagination-main pt-0 section-b-space">
                                <ul class="pagination-box">
                                    {!! $services->links() !!}
                                </ul>
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                    <div class="tab-pane fade" id="provider-review" role="tabpanel"
                        aria-labelledby="provider-review-tab">
                        <div class="reviews-main">
                            @php
                            $reviews = $provider?->reviews()?->get();
                            @endphp
                            @forelse($reviews as $review)

                            <div class="reviews">
                                <div class="person-detail">
                                    <img src="{{$review?->consumer?->media?->first()?->getUrl()}}" alt="feature">
                                    <div>
                                        <h6>{{ $review?->consumer?->name }}</h6>
                                        <p>“{{ $review?->description }}”</p>
                                    </div>
                                </div>
                                <div class="rating">
                                    <div class="rate">
                                        <img src="{{ asset('frontend/images/svg/star.svg')}}" alt="star"
                                            class="img-fluid star">
                                        <small>{{ $review?->rating }}</small>
                                    </div>
                                    <ul class="overview-list">
                                        <li>{{ $review?->created_at->diffForHumans() }}</li>
                                    </ul>
                                </div>
                            </div>
                            @empty
                            <div class="no-data-found">
                                <img class="img-fluid no-data-img" src="{{ asset('admin/images/svg/no-data.svg') }}" alt="">
                                <p>{{__('frontend::static.providers.reviews_not_found')}}</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<!-- Service List Section End -->

@forelse($services ?? [] as $service)
@includeIf('frontend.inc.modal',['service' => $service])
@empty
@endforelse

@endsection

@push('js')
@auth
<script src="{{ asset('frontend/js/custom-wishlist.js') }}"></script>
@endauth
@endpush