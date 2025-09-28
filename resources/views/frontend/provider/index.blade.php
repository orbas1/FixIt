@use('app\Helpers\Helpers')
@extends('frontend.layout.master')

@section('title', __('frontend::static.providers.providers'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{__('frontend::static.providers.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.providers.providers')}}</span>
</nav>
@endsection

@section('content')
<!-- Provider List Section Start -->
<section class="service-list-section section-b-space">
    <div class="container-fluid-lg">
        <div class="service-list-content">
            <div class="expert-content">
                <div class="row row-cols-xxl-5 row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1 g-sm-4 g-3">
                    @forelse ($providers as $provider)
                    <div class="col">
                        <div class="card gray-card">
                            <div class="gray-card-img">
                                @php
                                $profileImg = $provider?->media?->first()?->getUrl();
                                @endphp
                                @if(Helpers::isFileExistsFromURL($profileImg, true))
                                <img src="{{ $profileImg ?? asset('frontend/images/img-not-found.jpg')}}" alt="{{ $provider?->name }}" class="img-fluid profile-pic">
                                @else
                                <span class="profile-name initial-letter">{{ substr($provider?->name, 0, 1) }}</span>
                                @endif
                                @auth
                                <div class="like-icon" id="favouriteDiv" data-provider-id="{{ $provider?->id }}">
                                    <img class="img-fluid icon outline-icon" src="{{ asset('frontend/images/svg/heart-outline.svg')}}"
                                        alt="whishlist">
                                    <img class="img-fluid icon fill-icon" src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                                </div>
                                @endauth
                            </div>
                            <div class="card-body">
                                <div class="card-title">
                                    <a href="{{route('frontend.provider.details', $provider->slug)}}">
                                        <h4>{{ $provider?->name }}</h4>
                                    </a>
                                    <div class="rate">
                                        <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star" class="img-fluid star">
                                        <small>{{ $provider?->review_ratings }}</small>
                                    </div>
                                </div>
                                <div class="location">
                                    <i class="iconsax" icon-name="location"></i>
                                    <h5>{{ $provider?->primary_address?->state?->name }} -
                                        {{ $provider?->primary_address?->country?->name }}
                                    </h5>
                                </div>
                                <div class="card-detail">
                                    <p>{{ $provider?->primary_address?->address }},
                                        {{ $provider?->primary_address?->postal_code }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="no-data-found">
                        <p>{{__('frontend::static.providers.providers_not_found')}}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @if($providers ?? [])
        @if($providers?->lastPage() > 1)
        <div class="row">
            <div class="col-12">
                <div class="pagination-main section-b-space">
                    <ul class="pagination">
                        {!! $providers->links() !!}
                    </ul>
                </div>
            </div>
        </div>
        @endif
        @endif
</section>
<!-- Service List Section End -->
@endsection

@push('js')
@auth
<script src="{{ asset('frontend/js/custom-wishlist.js') }}"></script>
@endauth
@endpush