@use('app\Helpers\Helpers')
@use('App\Enums\FrontEnum')
@use('App\Enums\BidStatusEnum')
@use('App\Models\Category')
@use('App\Enums\SymbolPositionEnum')

@extends('frontend.layout.master')
@section('title', 'Custom Jobs')
@section('breadcrumb')
    <nav class="breadcrumb breadcrumb-icon">
        <a class="breadcrumb-item" href="{{ url('/') }}">{{ __('frontend::static.account.home') }}</a>
        <span class="breadcrumb-item active">{{ __('frontend::static.account.custom_jobs') }}</span>
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
                        <button
                            class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">{{ __('frontend::static.account.show_menu') }}</button>
                        <div class="profile-main h-100">
                            <div class="card m-0">
                                <div class="card-header">
                                    <div class="title-3 job-request-title">
                                         <h3> {{ __('frontend::static.account.custom_jobs') }}</h3>
                                    </div>
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#jobRequestModal"
                                        class="btn btn-solid w-auto d-inline">Request New Job</button>
                                </div>

                                <div class="card-body">
                                    <ul class="job-request-list">
                                        @forelse ($serviceRequests as $serviceRequest)
                                            <li class="job-request-box" data-bs-toggle="modal" data-bs-target="#jobRequestModal-{{ $serviceRequest?->id }}">
                                                @php
                                                    $locale = app()->getLocale();
                                                    $mediaItems = $serviceRequest
                                                        ->getMedia('image')
                                                        ->filter(function ($media) use ($locale) {
                                                            return $media->getCustomProperty('language') === $locale;
                                                        });

                                                    $imageUrl =
                                                        $mediaItems->count() > 0
                                                            ? $mediaItems->first()->getUrl()
                                                            : FrontEnum::getPlaceholderImageUrl();

                                                @endphp
                                                <div class="job-image">
                                                    <img src="{{ $imageUrl }}" class="job-img img-fluid" alt="">
                                                </div>

                                                <div class="job-content">
                                                    <div class="job-title">
                                                            <h3 title="{{ $serviceRequest->title }}" class="title-content">
                                                                {{ $serviceRequest->title }}</h3>
                                                        <span class="badge">{{ $serviceRequest->status }}</span>
                                                    </div>
                                                    <h5 class="price">
                                                        @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                            {{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($serviceRequest->initial_price, 2) }}
                                                        @else
                                                            {{ number_format($serviceRequest->initial_price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}
                                                        @endif
                                                    </h5>
                                                    <div class="job-date">
                                                        <h5 class="date"><i class="iconsax date-icon"
                                                                icon-name="calendar-1"></i>
                                                            {{ date('d-M-Y', strtotime($serviceRequest->created_at)) }}
                                                        </h5>
                                                        <i class="iconsax trash-icon" icon-name="trash"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteConfirmationModal-{{ $serviceRequest?->id }}"></i>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
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

    <!-- job request Section Start -->
    @forelse ($serviceRequests as $serviceRequest)
        <form method="post" action="{{ route('frontend.bid.update') }}">
            <div class="modal fade custom-job-modal" id="jobRequestModal-{{ $serviceRequest?->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Custom Job Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        @php
                            $locale = app()->getLocale();
                            $mediaItems = $serviceRequest?->getMedia('image')->filter(function ($media) use ($locale) {
                                return $media->getCustomProperty('language') === $locale;
                            });
                            $imageUrl =
                                $mediaItems->count() > 0
                                    ? $mediaItems->first()->getUrl()
                                    : FrontEnum::getPlaceholderImageUrl();
                        @endphp
                        <div class="modal-body">
                            <div class="job-details-image">
                                <img src="{{ $imageUrl }}" class="job-img img-fluid" alt="">
                            </div>

                            <div class="account-details-box">
                                {{-- <img src="{{ asset('frontend/images/account-bg.svg') }}" alt=""> --}}
                                <svg class="bg-color"><use xlink:href="{{ asset('frontend/images/account-bg.svg#accountBg') }}"></use></svg>
                                    <h4>Amount</h4>
                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)    
                                        <h3>{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($serviceRequest?->initial_price, 2) }}</h3>
                                    @else
                                        <h3>{{ number_format($serviceRequest?->initial_price, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</h3>
                                    @endif
                            </div>

                            <ul class="listing">
                                <li>
                                    <div class="listing-box">
                                        <i class="iconsax icon" icon-name="clock"> </i>
                                        <div>
                                            <h6 class="title-color listing-title">Time</h6>
                                            <h5>{{ $serviceRequest?->duration }} {{ $serviceRequest?->duration_unit }}</h5>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="listing-box">
                                        <i class="iconsax icon" icon-name="layers-1"> </i>
                                        <div>
                                            <h6 class="title-color listing-title">Category</h6>
                                            @php

                                                $categories = Category::whereIn('id', $serviceRequest?->category_ids)
                                                    ->pluck('title')
                                                    ->toArray();
                                            @endphp
                                            <h5 class="content-color white-nowrap mt-1">
                                                {{ implode(' , ', $categories) }}</h5>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="listing-box">
                                        <i class="iconsax icon" icon-name="tag-user"> </i>
                                        <div>
                                            <h6 class="title-color listing-title">Required Servicemen</h6>
                                            <h5>{{ $serviceRequest?->required_servicemen }} Servicemen</h5>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                            <div class="custom-job-title">
                                <h3 class="description-title">Description</h3>
                                <p class="description-content">{{ $serviceRequest?->description }} </p>
                            </div>

                            <ul class="job-provider-listing">
                                @forelse ($serviceRequest?->bids as $bid)
                                    <li class="job-provider-box">
                                        <div class="job-provider-image">
                                            @php
                                                $media = $bid->provider?->getFirstMedia('image');
                                                $imageUrl = $media ? $media->getUrl() : null;
                                            @endphp

                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" class="job-provider-img img-fluid"
                                                    alt="Image">
                                            @else
                                                <div class="initial-letter">
                                                    {{ strtoupper(substr($bid->provider?->name, 0, 1)) }}</div>
                                            @endif
                                        </div>
                                        <div class="job-provider-content">
                                            <div>
                                                <h5 class="job-provider-name">{{ $bid?->provider?->name }}</h5>
                                                <div class="d-flex align-items-center gap-1">
                                                    @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                        <h5 class="price">{{ Helpers::getDefaultCurrencySymbol() }}{{ number_format($bid?->amount, 2) }}</h5>
                                                    @else
                                                        <h5 class="price">{{ number_format($bid?->amount, 2) }} {{ Helpers::getDefaultCurrencySymbol() }}</h5>
                                                    @endif
                                                    <h5 class="rating">
                                                        <img src="{{ asset('frontend/images/svg/star.svg') }}" alt=""> {{ $bid?->provider?->review_ratings }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <input type="hidden" value="{{ $bid?->id }}" name="bid_id">
                                            @if ($bid->status == BidStatusEnum::REQUESTED)
                                                <div class="button-part">
                                                    <button type="submit" name="status" value="rejected"
                                                        class="btn btn-outline job-btn">Reject</button>
                                                    <button type="submit" name="status" value="accepted"
                                                        class="btn btn-solid job-btn">Accept</button>
                                                </div>
                                            @else
                                                <div class="job-title">
                                                    <span class="badge">{{ $bid->status }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @empty
                                @endforelse

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @empty
    @endforelse

    <div class="modal fade job-request-modal" id="jobRequestModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{ __('frontend::static.home_page.custom_job_request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('frontend.custom-job.store') }}" id="customJobForm" method="POST"
                    class="job-request-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
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
                            <div class="col-lg-6">
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
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="duration"
                                        class="form-check-label">{{ __('frontend::static.home_page.duration') }}</label>
                                    <div class="d-flex gap-2">
                                        <div class="w-100 position-relative">
                                            <i class="iconsax" icon-name="hourglass"></i>
                                            <input type="number" class="form-control form-control-white" name="duration"
                                                id="duration"
                                                placeholder="{{ __('frontend::static.home_page.enter_duration') }}"
                                                time-input>
                                        </div>
                                        <select class="form-select form-select-sm w-auto" name="duration_unit"
                                            id="duration_unit">
                                            @foreach (['hours' => 'Hours', 'minutes' => 'Minutes'] as $key => $option)
                                                <option class="option" value="{{ $key }}"
                                                    @if (old('duration_unit', $Request->duration_unit ?? '') === $key) selected @endif>
                                                    {{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
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
                            <div class="col-lg-6">
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
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="title"
                                        class="form-check-label">{{ __('frontend::static.home_page.categories') }}</label>

                                    <div class="error-div">
                                        <select class="form-control select-2 form-select-sm w-auto"
                                            data-placeholder="{{ __('static.service.select_categories') }}"
                                            search="true" name="category_ids[]" id="category_ids[]" multiple>
                                            @foreach ($serviceCategories as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (isset($default_categories) && in_array($key, $default_categories)) selected
                                                    @elseif (old('category_id') && in_array($key, old('category_id'))) selected @endif>
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
                        <button type="submit"
                            class="btn btn-solid mt-sm-4 mt-3 mx-auto">{{ __('frontend::static.home_page.post') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @forelse ($serviceRequests as $serviceRequest)
        <div class="modal fade delete-modal" id="deleteConfirmationModal-{{ $serviceRequest->id }}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    <div class="modal-body text-center">
                        <i class="iconsax modal-icon" icon-name="trash"></i>
                        <h3 class="modal-title">{{ __('static.delete_message') }}</h3>
                        {{-- <div class="main-img">
                        <i data-feather="trash-2"></i>
                    </div> --}}
                        {{-- <div class="modal-title">
                        {{ __('static.delete_message') }}
                    </div> --}}
                        <p>{{ __('static.delete_note') }}</p>
                    </div>
                    <form class="mb-0" action="{{ route('frontend.custom-job.delete', $serviceRequest->id) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline"
                                data-bs-dismiss="modal">{{ __('static.cancel') }}</button>
                            <button type="submit" class="btn btn-solid">{{ __('static.delete') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p>{{ __('static.no_service_requests') }}</p>
    @endforelse

@endsection
@push('js')
    <script>
        $('.select-2').select2();
        (function($) {
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
                    }
                },
            });
        })(jQuery);
    </script>
@endpush
