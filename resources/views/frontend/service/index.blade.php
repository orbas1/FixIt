@use('app\Helpers\Helpers')
@use('App\Enums\SymbolPositionEnum')

@extends('frontend.layout.master')
@push('css')
<!-- Range Slider css -->
<link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/range-slider.css') }}">
@endpush

@php
    $categories = Helpers::getCategories();
@endphp

@section('title', __('frontend::static.services.services'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{ __('frontend::static.services.home')}}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.services.services')}}</span>
</nav>
@endsection

@section('content')
<!-- Service List Section Start -->
<section class="service-list-section section-b-space">
    <div class="container-fluid-lg booking-sec">
        <div class="row service-list-content">
            <div class="col-xxl-3 col-xl-4 filter-sidebar">
                <div class="filter sticky booking-category">
                    <div class="card">
                        <div class="card-header">
                            <i class="iconsax close-btn filter-close d-xl-none d-flex" icon-name="arrow-left"></i>
                                <h3>{{ __('frontend::static.filter')}}</h3>
                            <a id="clear-all" class="ms-auto">{{ __('frontend::static.services.clear_all')}}</a>
                        </div>
                        <form action="{{ route('frontend.service.index') }}" method="GET">
                            <div class="accordion p-3 mb-0" id="provider">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="providerItem">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProvider" aria-expanded="true" aria-controls="collapseProvider">
                                            {{ __('frontend::static.services.sort_by')}}
                                        </button>
                                    </h2>
                                    <div id="collapseProvider" class="accordion-collapse collapse show" aria-labelledby="collapseProvider" data-bs-parent="#provider">
                                        <div class="accordion-body">
                                            <div class="filter-body">
                                                <div class="service">
                                                    <div class="d-flex flex-column gap-2">
                                                        <div class="form-check">
                                                            <input type="radio" id="test1" name="provider_sortBy" class="form-radio-input" value="high-exp" @checked(request()->provider_sortBy == 'high-exp')>
                                                            <label for="test1">{{ __('frontend::static.services.highest_exp')}}</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input type="radio" id="test3" name="provider_sortBy" class="form-radio-input" value="high-serv" @checked(request()->provider_sortBy == 'high-serv')>
                                                            <label for="test3">{{ __('frontend::static.services.highest_served')}}</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input type="radio" id="test2" name="provider_sortBy" class="form-radio-input" value="low-exp" @checked(request()->provider_sortBy == 'low-exp')>
                                                            <label for="test2">{{ __('frontend::static.services.lowest_exp')}}</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input type="radio" id="test4" name="provider_sortBy" class="form-radio-input" value="low-serv" @checked(request()->provider_sortBy == 'low-serv')>
                                                            <label for="test4">{{ __('frontend::static.services.lowest_served')}}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="search-provider">
                                                    <div class="accordion mb-0" id="search-provider">
                                                        <div class="accordion-item" id="search-providerItem">
                                                            <div class="search-div">
                                                                <div class="form-group d-flex">
                                                                    <i class="iconsax" icon-name="user-1"></i>
                                                                    <input type="search" id="accordion_search_bar" placeholder="Search" autocomplete="off" />
                                                                </div>
                                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsesearch-provider" aria-expanded="true" aria-controls="collapsesearch-provider"></button>
                                                            </div>
                                                            <div id="collapsesearch-provider" class="accordion-collapse collapse show" aria-labelledby="collapsesearch-provider" data-bs-parent="#search-provider">
                                                                <div class="accordion-body mb-0 pb-0">
                                                                    <div class="search-body custom-scroll">
                                                                        @php
                                                                            $providers = $providers->get();
                                                                        @endphp
                                                                            <input type="hidden" name="provider" id="select-provider" class="form-check-input" value="">
                                                                        @if(count($providers ?? []))
                                                                            <p id="no-results-message" class="no-results no-provider-results-message" style="display:none;">
                                                                                {{ __('frontend::static.services.providers_not_found')}}
                                                                            </p>
                                                                        @endif
                                                                        @forelse($providers as $provider)
                                                                        <div class="form-check provider-item">
                                                                            <input type="checkbox" class="form-check-input provider-input" value="{{ $provider?->id }}">
                                                                            <ul>
                                                                                <li class="name">
                                                                                    {{ $provider?->name }}
                                                                                </li>
                                                                                <li class="served">
                                                                                    {{ $provider?->served }}
                                                                                    {{ __('frontend::static.services.served')}}
                                                                                </li>
                                                                                <li class="year">
                                                                                    {{ $provider?->experience_duration }}
                                                                                    {{ $provider?->experience_interval }}
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        @empty
                                                                            <p id="no-results-message" class="no-results no-provider-results-message">
                                                                                {{ __('frontend::static.services.providers_not_found')}}
                                                                            </p>
                                                                        @endforelse
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion p-3 mb-0" id="category">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="categoryItem">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsecategory" aria-expanded="true" aria-controls="collapsecategory">
                                            {{ __('frontend::static.services.categories')}}
                                        </button>
                                    </h2>
                                    <div id="collapsecategory" class="accordion-collapse collapse show" aria-labelledby="collapsecategory" data-bs-parent="#category">

                                        <div class="accordion-body">
                                            <div class="search-div">
                                                <input type="search" autocomplete="off" class="form-control form-control-white" id="accordion_category_search_bar" placeholder="Search" />
                                            </div>
                                            <input type="hidden" name="categories" id="select-category" class="form-check-input" value="">
                                            <p id="no-results-message" class="no-results mt-3 no-category-results-message" style="display: none;">Category not found</p>
                                            <div class="category-body">
                                                <ul class="category-list custom-scroll">
                                                    @forelse($categories as $category)
                                                    <li class="form-check category-item ps-0 pe-2">
                                                        <label class="form-check-label">
                                                            <img src="{{Helpers::isFileExistsFromURL($category?->media?->first()?->getUrl(), true) }}" alt="">
                                                            <span class="name" title="{{$category?->title }}">{{$category?->title }}</span>
                                                        </label>
                                                        <input type="checkbox" class="form-check-input categories-input" value="{{$category?->slug}}"></input>
                                                    </li>
                                                    @empty
                                                        <li class="form-check category-item no-category">
                                                            {{ __('frontend::static.services.categories_not_found')}}
                                                        </li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion p-3 mb-0" id="range">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="rangeItem">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapserange">
                                            {{ __('frontend::static.services.price_range')}}
                                        </button>
                                    </h2>
                                    <div id="collapserange" class="accordion-collapse collapse show" data-bs-parent="#range">
                                        <div class="accordion-body price-range-box">
                                            <input id="price-range" name="price" type="text" class="range-slider" data-min="{{ $services->min('service_rate') }}" data-max="{{ $services->max('service_rate') }}" 
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                data-symbol="{{ Helpers::getDefaultCurrencySymbol() }}"
                                            @else
                                                data-symbol="{{ Helpers::getDefaultCurrencySymbol() }} "
                                            @endif>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion p-3 mb-0" id="rating">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="ratingItem">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapserating" aria-expanded="true" aria-controls="collapserating">
                                            {{ __('frontend::static.services.rattings')}}
                                        </button>
                                    </h2>
                                    <div id="collapserating" class="accordion-collapse collapse show" aria-labelledby="collapserating" data-bs-parent="#rating">
                                        <div class="accordion-body">
                                            <div class="star-rating">
                                                @php
                                                    $rating = request()->input('rating');
                                                @endphp
                                                <div>
                                                    <input type="radio" id="5-stars" name="rating" value="5" {{ $rating == 5 ? 'checked' : '' }} />
                                                    <label for="5-stars" class="star"><i class="iconsax" icon-name="star"></i>5</label>
                                                </div>
                                                <div>
                                                    <input type="radio" id="4-stars" name="rating" value="4" {{ $rating == 4 ? 'checked' : '' }} />
                                                    <label for="4-stars" class="star"><i class="iconsax" icon-name="star"></i>4</label>
                                                </div>
                                                <div>
                                                    <input type="radio" id="3-stars" name="rating" value="3" {{ $rating == 3 ? 'checked' : '' }} />
                                                    <label for="3-stars" class="star"><i class="iconsax" icon-name="star"></i>3</label>
                                                </div>
                                                <div>
                                                    <input type="radio" id="2-stars" name="rating" value="2" {{ $rating == 2 ? 'checked' : '' }} />
                                                    <label for="2-stars" class="star"><i class="iconsax" icon-name="star"></i>2</label>
                                                </div>
                                                <div>
                                                    <input type="radio" id="1-star" name="rating" value="1" {{ $rating == 1 ? 'checked' : '' }} />
                                                    <label for="1-star" class="star"><i class="iconsax" icon-name="star"></i>1</label>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->
                            <div class="card-footer">
                                <button type="submit"
                                    class="btn btn-solid">{{ __('frontend::static.services.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="col-xxl-9 col-xl-8">
                <div class="filter-div">
                    <div class="d-xl-none d-block mb-3">
                        <a href="javascript:void(0)" class="btn btn-solid filter-btn w-max">
                            Filter
                        </a>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-2 row-cols-xxl-3 g-sm-4 g-3">
                    @forelse($services as $service)
                    <div class="col">
                        <div class="card">
                            @if($service->discount)
                                <div class="discount-tag">{{ $service->discount }}%</div>
                            @endif
                            @auth
                            <div class="like-icon" id="favouriteDiv" data-service-id="{{ $service?->id }}">
                                <img class="img-fluid icon outline-icon" src="{{ asset('frontend/images/svg/heart-outline.svg')}}" alt="whishlist">
                                <img class="img-fluid icon fill-icon" src="{{ asset('frontend/images/svg/heart-fill.svg')}}" alt="wishlisted">
                            </div>
                            @endauth
                            <div class="overflow-hidden b-r-5">
                                <a href="{{route('frontend.service.details', $service?->slug)}}" class="card-img">
                                    <img src="{{ $service?->web_img_thumb_url }}" alt="{{ $service?->title }}" class="img-fluid">
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="service-title">
                                    <h4>
                                        <a href="{{ route('frontend.service.details', $service?->slug) }}" title="{{$service?->title }}">{{ $service?->title }}</a>
                                    </h4>
                                    <div class="d-flex align-items-center gap-1">
                                        @if (!empty($service?->discount) && $service?->discount > 0)
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <del>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->price) }}</del>
                                                <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                            @else
                                                <del>{{ Helpers::covertDefaultExchangeRate($service->price) }} {{ Helpers::getDefaultCurrencySymbol() }}</del>
                                                <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                            @endif
                                        @else
                                            @if (Helpers::getDefaultCurrency()->symbol_position === SymbolPositionEnum::LEFT)
                                                <small>{{ Helpers::getDefaultCurrencySymbol() }}{{ Helpers::covertDefaultExchangeRate($service->service_rate) }}</small>
                                            @else
                                                <small>{{ Helpers::covertDefaultExchangeRate($service->service_rate) }} {{ Helpers::getDefaultCurrencySymbol() }}</small>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="service-detail mt-1">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <ul>
                                            <li class="time">
                                                <i class="iconsax" icon-name="clock"></i>
                                                <span>{{ $service?->duration }}{{ $service?->duration_unit === 'hours' ? 'h' : 'm' }}</span>
                                            </li>
                                            <li class="w-auto service-person">
                                                <img src="{{ asset('frontend/images/svg/services-person.svg') }}" alt="">
                                                <span>{{ $service?->required_servicemen }}</span>
                                            </li>
                                        </ul>
                                        <h6 class="service-type mt-2"><span> {{ Helpers::formatServiceType($service?->type) }}</span></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer border-top-0">
                                <div class="footer-detail">
                                    <a href="{{route('frontend.provider.details',['slug' => $service?->user?->slug])}}">
                                        <img src="{{ Helpers::isFileExistsFromURL($service?->user?->media?->first()?->getURL(), true) }}" alt="feature" class="img-fluid">
                                    </a>
                                    <div>
                                        <p>{{ $service?->user?->name }}</p>
                                        <div class="rate">
                                            <img src="{{ asset('frontend/images/svg/star.svg') }}" alt="star" class="img-fluid star">
                                            <small>{{ $service?->user?->review_ratings ?? 'Unrated' }}</small>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn book-now-btn btn-solid w-auto" id="bookNowButton" data-bs-toggle="modal" data-bs-target="#bookServiceModal-{{ $service->id }}" data-login-url="{{ route('frontend.login') }}" data-check-login-url="{{ route('frontend.check.login') }}" data-service-id="{{ $service->id }}">
                                    <span data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        data-bs-custom-class="custom-tooltip"
                                        data-bs-title="{{ __('frontend::static.services.book_now') }}">{{ __('frontend::static.services.book_now') }}</span>
                                    <span class="spinner-border spinner-border-sm" style="display: none;"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="no-data-found">
                        <svg class="no-data-img">
                            <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                        </svg>
                        {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                        <p>{{ __('frontend::static.services.services_not_found')}}</p>
                        @endforelse
                    </div>
                    @if(count($services ?? []))
                    @if($services?->lastPage() > 1)
                    <div class="col-12">
                        <div class="pagination-main pt-0">
                            <ul class="pagination-box">
                                {!! $services->links() !!}
                            </ul>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
</section>
@forelse($services as $service)
@includeIf('frontend.inc.modal',['service' => $service])
@empty
@endforelse
<!-- Service List Section End -->
@endsection

@push('js')
<!-- Swiper js -->
<script src="{{ asset('frontend/js/swiper.js') }}"></script>

<!-- Range slider js -->
<script src="{{ asset('frontend/js/range-slider/ion.rangeSlider.min.js') }}"></script>
<script src="{{ asset('frontend/js/range-slider/rangeslider-script.js') }}"></script>

@auth
<script src="{{ asset('frontend/js/custom-wishlist.js') }}"></script>
@endauth

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    $(function() {
        "use strict";

        $('#clear-all').click(function(e) {
            e.preventDefault();
            window.history.replaceState(null, null, location.pathname);
            location.reload();
        });

        $('#accordion_search_bar').on('keyup', function() {
            let searchTerm = $(this).val().toLowerCase(),
                hasResults = false;
            $('.provider-item').each(function() {
                let showItem = $(this).find('.name').text().toLowerCase().includes(searchTerm);
                $(this).toggle(showItem);
                hasResults = hasResults || showItem;
            });

            $('.no-provider-results-message').toggle(!hasResults);
        });

        $('#accordion_category_search_bar').on('keyup', function() {
            let searchTerm = $(this).val().toLowerCase(),
                hasResults = false;
            $('.category-item').each(function() {
                let showItem = $(this).find('.name').text().toLowerCase().includes(searchTerm);
                $(this).toggle(showItem);
                hasResults = hasResults || showItem;
            });

            $('.no-category-results-message').toggle(!hasResults);
        });

        var urlParams = new URLSearchParams(window.location.search);
        var providerValues = urlParams.get("provider");
        providerValues?.split(",").forEach(val =>
            $(".provider-input[value='" + val + "']").prop("checked", true)
        );

        var urlParams = new URLSearchParams(window.location.search);
        var categoryValues = urlParams.get("categories");
        categoryValues?.split(",").forEach(val =>
            $(".categories-input[value='" + val + "']").prop("checked", true)
        );

        $('.provider-input').change(function() {
            var selectedIds = $('.provider-input:checked').map(function() {
                return this.value;
            }).get().join(',');
            if (selectedIds) {
                $('#select-provider').val(selectedIds);
            }
        });

        $('#accordion_categoy_search_bar').on('keyup', function() {
            let searchTerm = $(this).val().toLowerCase(),
                hasResults = false;
            $('.category-item').each(function() {
                let showItem = $(this).find('.name').text().toLowerCase().includes(searchTerm);
                $(this).toggle(showItem);
                hasResults = hasResults || showItem;
            });

            $('.no-provider-results-message').toggle(!hasResults);
        });

        
        $('.categories-input').change(function() {
            var selectedIds = $('.categories-input:checked').map(function() {
                return this.value;
            }).get().join(',');
            if (selectedIds) {
                $('#select-category').val(selectedIds);
            }
        });

        $('.category-item').on('click', function(e) {
            const checkbox = $(this).find('.categories-input');
            if (e.target !== checkbox[0]) {
                checkbox.prop('checked', !checkbox.prop('checked'));
            }

            var selectedIds = $('.categories-input:checked').map(function() {
                return this.value;
            }).get().join(',');
            if (selectedIds) {
                $('#select-category').val(selectedIds);
            }
        });

        $('.categories-input').on('click', function(e) {
            e.stopPropagation();
        });


        $('.provider-item').on('click', function(e) {
            const checkbox = $(this).find('.provider-input');
            if (e.target !== checkbox[0]) {
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });

        $('.provider-input').on('click', function(e) {
            e.stopPropagation();
        });

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