@use('app\Helpers\Helpers')

@extends('frontend.layout.master')
@section('title',   __('frontend::static.account.profile'))
@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.profile') }}</span>
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
                                    <h3>{{ __('frontend::static.account.dashboard') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="widgets">
                                    <div class="row g-sm-4 g-3">
                                        <div class="col-xxl-4 col-xl-6 col-lg-4 col-sm-6">
                                            <a href="{{ route('frontend.account.wallet') }}" class="text-decoration-none text-dark">
                                                <div class="card">
                                                    <div class="widget-data">
                                                        <div class="data">
                                                            <h5>{{ __('frontend::static.account.wallet_balance') }}</h5>
                                                            <h3>{{ Helpers::getSettings()['general']['default_currency']->symbol }} {{ auth()?->user()?->wallet?->balance ?? 0.0 }}</h3>
                                                        </div>
                                                        <div class="data-icon">
                                                            <div class="dot"></div>
                                                            <i class="iconsax" icon-name="wallet-3"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-xxl-4 col-xl-6 col-lg-4 col-sm-6">
                                            <a href="{{ route('frontend.booking.index') }}" class="text-decoration-none text-dark">
                                                <div class="card">
                                                    <div class="widget-data">
                                                        <div class="data">
                                                            <h5>{{ __('frontend::static.account.pending_services') }}</h5>
                                                            <h3>{{auth()?->user()?->getPendingServiceAttribute() ?? 0}} {{ __('frontend::static.account.service') }}</h3>
                                                        </div>
                                                        <div class="data-icon">
                                                            <div class="dot"></div>
                                                            <i class="iconsax" icon-name="receipt-list"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-xxl-4 col-xl-6 col-lg-4 col-sm-6">
                                            <a href="{{ route('frontend.booking.index') }}" class="text-decoration-none text-dark">
                                                <div class="card">
                                                    <div class="widget-data">
                                                        <div class="data">
                                                            <h5>{{ __('frontend::static.account.completed_services') }}</h5>
                                                            <h3>{{auth()?->user()?->getCompletedServiceAttribute() ?? 0}} {{ __('frontend::static.account.service') }}</h3>
                                                        </div>
                                                        <div class="data-icon">
                                                            <div class="dot"></div>
                                                            <i class="iconsax" icon-name="tick-circle"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-data">
                                    <h3 class="mb-sm-3 mb-2 mt-3">
                                    {{ __('frontend::static.account.profile_setting') }}
                                    </h3>
                                    <div class="card">
                                        <div class="card-body p-sm-2 p-0">
                                            <div class="row g-sm-4 g-3">
                                                <div class="col-lg-6">
                                                    <div class="personal-detail">
                                                        <div class="form-group">
                                                            <label for="name">{{ __('frontend::static.account.name') }}</label>
                                                            <h4 class="value">{{ auth()?->user()?->name ?? '-' }}</h4>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="phone">{{ __('frontend::static.account.phone') }}</label>
                                                            <h4 class="value">
                                                                @if(auth()?->user()?->code && auth()?->user()?->phone)
                                                                +{{ auth()?->user()?->code }} {{ auth()?->user()?->phone }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </h4>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="email"> {{ __('frontend::static.account.email') }}</label>
                                                            <h4 class="value">{{ auth()?->user()?->email ?? '-' }}</h4>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="address"> {{ __('frontend::static.account.address') }}</label>
                                                            <h4 class="value">
                                                                <h4 class="value">
                                                                    @php
                                                                    $address=auth()?->user()?->getPrimaryAddressAttribute()->address ?? null;
                                                                    $state=auth()?->user()?->getPrimaryAddressAttribute()?->state?->name ?? null;
                                                                    $postal_code=auth()?->user()?->getPrimaryAddressAttribute()?->postal_code ?? null;
                                                                    $country=auth()?->user()?->getPrimaryAddressAttribute()?->country?->name ?? null;
                                                                    @endphp
                                                                    @if($address)
                                                                    {{ $address}} , {{$state}} - {{$postal_code}} , {{$country}}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </h4>
                                                            </h4>
                                                        </div>
                                                        <div class="form-group">
                                                            <div>
                                                                <label for="password"> {{ __('frontend::static.account.password') }} </label>
                                                                <a href="#change-password" data-bs-toggle="modal"> <i class="iconsax edit-btn" icon-name="edit-2"></i> </a>
                                                            </div>
                                                            <h4 class="value">{{auth()?->user()?->password ? '● ● ● ● ● ●' : '-' }}  </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="profile-setting-img">
                                                        <img src="{{ asset('frontend/images/girl-on-chair.png')}}" alt="girl" class="girl-on-chair img-fluid">
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
        </div>
    </div>
</section>
<!-- Service List Section End -->

@endsection
@push('js')
<script src="{{ asset('frontend/js/jquery-ui.min.js')}}"></script>
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            let profileFormRules = {
                "image": {
                    accept: "image/jpeg, image/png"
                },
                "name": "required",
                "email": "required",
                "phone": "required"
            };

            $("#profileForm").validate({
                ignore: [],
                rules: profileFormRules,
                messages: {
                    "image": {
                        accept: "Only JPEG and PNG files are allowed.",
                    },
                }
            });

            $('#submitProfileForm').on('click', function() {
                if ($("#profileForm").valid()) {
                    $('#profileForm').submit();
                }
            });
        });

    })(jQuery);
</script>
@endpush