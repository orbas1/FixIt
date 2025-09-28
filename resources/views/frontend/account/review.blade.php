@use('app\Helpers\Helpers')
@extends('frontend.layout.master')
@section('title', 'Reviews')
@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.reviews') }}</span>
</nav>
@endsection
@section('content')
<!-- Service List Section Start -->
<section class="section-b-space">
    <div class="container-fluid-md">
        <div class="profile-body-wrapper">
            <div class="row">
                @includeIf('frontend.account.sidebar')
                <div class="col-xxl-9 col-xl-8">
                    <button class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">
                        {{ __('frontend::static.account.show_menu') }}
                    </button>
                    <div class="profile-main h-100">
                        <div class="card m-0">
                            <div class="card-header">
                                <div class="title-3">
                                    <h3>{{ __('frontend::static.account.my_reviews') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="review-main">
                                    @forelse ($reviews as $review)
                                    <li class="review-list">
                                        <div class="review">
                                            <div class="review-img">
                                                @php
                                                $profileImg = auth()->user()->getFirstMedia('image')?->getUrl();
                                                @endphp
                                                @if(Helpers::isFileExistsFromURL($profileImg))
                                                <img src="{{ Helpers::isFileExistsFromURL($profileImg, true) }}" alt="" class="img-fluid">
                                                @else
                                                <span
                                                    class="profile-name initial-letter">{{ substr(auth()->user()?->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div class="review-note">
                                                <div class="name-date">
                                                    <div>
                                                        <h3>{{ auth()->user()->name }}</h3>
                                                        @if ($review->service_id)
                                                        <h6>{{ $review->created_at->diffForHumans() }}</h6>
                                                        @else
                                                        <div class="d-flex align-items-center gap-2 mt-1">
                                                            <h6>{{ $review->created_at->diffForHumans() }}</h6>
                                                            <div class="rate mt-0">
                                                                <img src="{{ asset('frontend/images/svg/star.svg') }}"
                                                                    alt="star" class="img-fluid star">
                                                                <small>{{ number_format($review->rating, 1) }}</small>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div class="notify-time">

                                                        <div class="review-action-box">
                                                            <button type="button" data-bs-toggle="modal"
                                                                data-bs-target="#editReviewModal-{{ $review->id }}">
                                                                <i class="iconsax edit" icon-name="edit-2"></i>
                                                            </button>
                                                            <button type="button" data-bs-toggle="modal"
                                                                data-bs-target="#deleteReviewModel-{{ $review->id }}">
                                                                <i class="iconsax delete" icon-name="trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($review->service_id)
                                                <h5>{{ $review?->service?->title }}</h5>
                                                @else
                                                <h5>{{ $review->serviceman->name }}</h5>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <p>{{ $review->description }}</p>
                                        </div>
                                    </li>
                                    @includeIf('frontend.inc.modals.review', ['review' => $review])
                                    @empty
                                    <div class="no-data-found">
                                        <svg class="no-data-img">
                                            <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                                        </svg>
                                        {{-- <img class="img-fluid no-data-img"
                                            src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                                        <p>{{ __('frontend::static.account.reviews_not_found') }}</p>
                                    </div>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Service List Section End -->
@endsection