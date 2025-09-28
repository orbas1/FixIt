@use('app\Helpers\Helpers')
@php
$savedAddresses = auth()?->user()?->addresses;
@endphp
@extends('frontend.layout.master')
@section('title', 'Addresses')
@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.addresses') }}</span>
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
                    <button class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">Show
                        {{ __('frontend::static.account.menu') }}</button>
                    <div class="profile-main h-100">
                        <div class="card m-0">
                            <div class="card-header">
                                <div class="title-3">
                                    <h3>{{ __('frontend::static.account.addresses') }}</h3>
                                </div>
                                <button type="button" class="edit-option text-theme-color" data-bs-toggle="modal"
                                    data-bs-target="#locationModal">
                                    + {{ __('frontend::static.account.add_new_address') }}
                                </button>
                            </div>
                            <div class="card-body service-booking">
                                <div class="row g-sm-4 g-3">
                                    @if (count(auth()?->user()?->addresses ?? []))
                                    @foreach($savedAddresses as $savedAddress)
                                    <div class="col-md-6">
                                        <div class="card delivery-location">
                                            <div class="location-header">
                                                <div
                                                    class="@if ($savedAddress?->is_primary) active-icon @else location-icon @endif">
                                                    @if ($savedAddress?->is_primary)
                                                    <img src="{{ asset('frontend/images/svg/tick.svg') }}" alt="tick">
                                                    @else
                                                    {{-- <img src="{{ asset('frontend/images/svg/home-1.svg') }}" alt="home"> --}}
                                                    <svg>
                                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#home') }}"></use>
                                                    </svg>
                                                    @endif
                                                </div>
                                                <div class="delivery-name">
                                                    <div class="name">
                                                        <h4>{{ $savedAddress?->alternative_name ?? auth()?->user()?->name }}</h4>
                                                        <span>({{ $savedAddress?->code ?? auth()?->user()?->code }})
                                                            {{ $savedAddress?->alternative_phone ?? auth()?->user()?->phone }}</span>
                                                    </div>
                                                    <span class="badge primary-light-badge">{{ $savedAddress?->type ?? auth()?->user()?->type }}</span>
                                                </div>
                                            </div>
                                            <div class="address">
                                                <label>{{ __('frontend::static.account.addresses') }} :</label>
                                                <p>{{ $savedAddress?->address }}
                                                    ,{{ $savedAddress?->state?->name }} -
                                                    {{ $savedAddress?->postal_code }},
                                                    {{ $savedAddress?->country?->name }}
                                                </p>

                                            </div>
                                            <div class="address-bottom-box">
                                                <div class="action">
                                                    <button type="button" class="btn btn-outline" data-bs-toggle="modal"
                                                        data-bs-target="#editlocationModal-{{ $savedAddress?->id }}">
                                                        <i class="iconsax icon"
                                                            icon-name="edit-1"></i>{{ __('frontend::static.account.edit') }}
                                                    </button>
                                                    <button type="button" class="btn btn-outline" data-bs-toggle="modal"
                                                        data-bs-target="#deleteaddressModel-{{ $savedAddress?->id }}">
                                                        <i class="iconsax icon" icon-name="trash"></i>
                                                        {{ __('frontend::static.account.delete') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit location modal -->
                                    <div class="modal fade address-modal"
                                        id="editlocationModal-{{ $savedAddress?->id }}">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <form action="{{ route('frontend.address.update', $savedAddress?->id) }}"
                                                id="editAddressForm" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h3 class="modal-title m-0"
                                                            id="editlocationModalLabel-{{ $savedAddress?->id }}">
                                                            {{ __('frontend::static.account.edit_location') }}
                                                        </h3>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @include('frontend.address.fields', [
                                                        'address' => $savedAddress,
                                                        ])
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline"
                                                            data-bs-dismiss="modal">{{ __('frontend::static.account.close') }}</button>
                                                        <button type="submit"
                                                            class="btn btn-solid submitBtn spinner-btn">{{ __('frontend::static.account.submit') }}</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    @endforeach
                                    @else
                                    <div class="no-data-found">
                                        <svg class="no-data-img">
                                            <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                                        </svg>
                                        {{-- <img class="img-fluid no-data-img"
                                            src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                                        <p>{{ __('frontend::static.account.address_not_found') }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Service List Section End -->
<!-- location modal -->
<div class="modal fade address-modal" id="locationModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('frontend.address.store') }}" id="addressForm" method="post">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addaddressModalLabel">{{ __('static.address.add') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    @include('frontend.address.fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        data-bs-dismiss="modal">{{ __('frontend::static.account.close') }}</button>
                    <button type="submit"
                        class="btn btn-solid submitBtn spinner-btn">{{ __('frontend::static.account.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($savedAddresses as $savedAddress)
<!-- Delete Address modal -->
<div class="modal fade delete-modal" id="deleteaddressModel-{{ $savedAddress?->id }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            <div class="modal-body text-center">
                <i class="iconsax modal-icon" icon-name="trash"></i>
                <h3>Delete Item? </h3>
                <p class="mx-auto">
                    {{ __('frontend::static.account.confirm_address') }}
                </p>
            </div>
            <form action="{{ route('frontend.account.address.delete', $savedAddress->id) }}" method="post">
                @method('DELETE')
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                        data-bs-dismiss="modal">{{ __('frontend::static.account.no') }}</button>
                    <button type="submit" class="btn btn-solid" data-bs-toggle="modal"
                        data-bs-target="#successfullyDeleteaddressModel">{{ __('frontend::static.account.yes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection