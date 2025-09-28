@use('app\Helpers\Helpers')
@php
    $homePage = Helpers::getCurrentHomePage();
@endphp
<div class="col-xxl-3 col-xl-4">
    <div class="profile-wrapper filter-sidebar filter">
        <i class="iconsax close filter-close" icon-name="add"></i>
        </button>
        <div class="profile">
            @php
                $profieImg = auth()?->user()?->getFirstMediaUrl('image');
            @endphp
            <div class="profile-img update-img">
                @if (Helpers::isFileExistsFromURL($profieImg))
                    <img class="align-self-center profile-image pull-right img-fluid blur-up lazyloaded" src="{{ Helpers::isFileExistsFromURL($profieImg, true) }}" alt="header-user">
                @else
                    <span class="profile-name initial-letter">{{ substr(auth()->user()?->name, 0, 1) }}</span>
                @endif
                <button type="button" class="edit-modal" data-bs-toggle="modal" data-bs-target="#personalDetailModal">
                    <i class="iconsax" icon-name="edit-2"></i>
                </button>
            </div>
            <div class="profile-detail">
                <svg>
                    <use xlink:href="{{ asset('frontend/images/profile-bg.svg#profile-bg') }}"></use>
                </svg>
                <h5>{{ auth()?->user()?->name }}</h5>
                <p>
                    <i class="iconsax" icon-name="mail"></i>
                    {{ auth()?->user()?->email }}
                </p>
            </div>
        </div>
        <div class="profile-settings custom-scroll">
            <nav class="navbar navbar-expand-md p-0">
                <button class="navbar-toggler d-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainnavbarNav">
                    <i class="iconsax" icon-name="mail"></i>
                </button>
                <div class="collapse navbar-collapse" id="mainnavbarNav">
                    <div class="menu-panel">
                        <button data-bs-toggle="collapse" data-bs-target="#mainnavbarNav"
                            class="mainnav-close d-block d-md-none">
                        </button>
                        <ul class="nav nav-tabs menu-wrapper">
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('account/profile*') ? 'active' : '' }}"
                                    href="{{ route('frontend.account.profile.index') }}">
                                    <i class="iconsax deactivate-icon" icon-name="home-1"></i>
                                    <svg class="active-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#home') }}"></use>
                                    </svg>
                                    <span>{{ __('frontend::static.account.dashboard') }}</span>
                                </a>
                            </li>
                            @if (Helpers::getSettings()['service_request']['status'] && $homePage['custom_job']['status'])
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('account/custom-job*') ? 'active' : '' }}"
                                    href="{{ route('frontend.account.customJob') }}">
                                    {{-- <i class="iconsax deactivate-icon" icon-name="bell-1"></i> --}}
                                    <svg class="deactive-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#global-line') }}"></use>
                                    </svg>
                                    <svg class="active-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#global-fill') }}"></use>
                                    </svg>
                                    <span>{{ __('frontend::static.account.custom_jobs') }}</span>
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('account/notification*') ? 'active' : '' }}"
                                    href="{{ route('frontend.account.notification') }}">
                                    <i class="iconsax deactivate-icon" icon-name="bell-1"></i>
                                    <svg class="active-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#notification') }}"></use>
                                    </svg>
                                    <span>{{ __('frontend::static.account.notifications') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('account/wallet*') ? 'active' : '' }}"
                                    href="{{ route('frontend.account.wallet') }}">
                                    <i class="iconsax deactivate-icon" icon-name="wallet-3"></i>
                                    <svg class="active-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#wallet') }}"></use>
                                    </svg>
                                    <span>{{ __('frontend::static.account.my_wallet_sidebar') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('account/address*') ? 'active' : '' }}"
                                    href="{{ route('frontend.account.address') }}">
                                    <i class="iconsax deactivate-icon" icon-name="location"></i>
                                    <svg class="active-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#location') }}"></use>
                                    </svg>
                                    <span>{{ __('frontend::static.account.saved_addresses') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link  {{ Request::is('account/review*') ? 'active' : '' }}"
                                    href="{{ route('frontend.account.review') }}">
                                    <i class="iconsax deactivate-icon" icon-name="star"></i>
                                    <svg class="active-icon">
                                        <use xlink:href="{{ asset('frontend/images/svg/profile-svg.svg#star-rate') }}"></use>
                                    </svg>
                                    <span>{{ __('frontend::static.account.my_reviews') }}</span>
                                </a>
                            </li>
                            <li class="profile-logout">
                                <a href="{{ route('frontend.logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="nav-link">
                                    <i class="iconsax"
                                        icon-name="logout-1"></i><span>{{ __('frontend::static.account.logout') }}</span>
                                </a>
                                <form action="{{route('frontend.logout')}}" method="POST" class="d-none"
                                    id="logout-form">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>

<!-- update personal detail modal -->
<div class="modal fade profile-update-modal" id="personalDetailModal" tabindex="-1"
    aria-labelledby="personalDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="personalDetailModalLabel">
                    {{ __('frontend::static.account.update_profile') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('frontend.account.profile.update') }}" method="POST" id="profileForm"
                enctype="multipart/form-data">
                <div class="modal-body">
                    @method('PUT')
                    @csrf
                    @php
                    $profileImg = auth()?->user()?->getFirstMediaUrl('image');
                    @endphp
                    <div class="update-img">
                        @if (Helpers::isFileExistsFromURL($profileImg))
                        <img class="align-self-center profile-image pull-right img-fluid blur-up lazyloaded"
                            src="{{ Helpers::isFileExistsFromURL($profieImg, true) }}" alt="header-user">
                        @else
                        <span class="profile-name initial-letter">{{ substr(auth()->user()?->name, 0, 1) }}</span>
                        @endif
                        <label class="custom-file-upload">
                            <input type="file" name="image" accept=".jpg, .png, .jpeg" value="{{ old('image') }}">
                            <i class="iconsax update-profile" icon-name="edit-2"></i>
                            @error('image')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </label>
                    </div>
                    <div class="update-detail">
                        <div class="form-group">
                            <label for="name" class="mt-0">{{ __('frontend::static.account.name') }}<span
                                    class="required-span">*</span></label>
                            <div class="position-relative">
                                <i class="iconsax" icon-name="user-1"></i>
                                <input class="form-control form-control-white" id="name"
                                    placeholder="{{ __('frontend::static.account.enter_name') }}" name="name"
                                    type="text" value="{{auth()?->user()?->name}}">
                            </div>
                            @error('name')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email" class="mt-0">{{ __('frontend::static.account.email') }}<span
                                    class="required-span">*</span></label>
                            <div class="position-relative">
                                <i class="iconsax" icon-name="mail"></i>
                                <input class="form-control form-control-white" id="email"
                                    placeholder="{{ __('frontend::static.account.enter_email') }}" name="email"
                                    type="email" value="{{auth()?->user()?->email}}">
                            </div>
                            @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="mt-0" for="phone">{{ __('static.phone') }} <span
                                    class="required-span">*</span></label>
                            <div class="position-relative profile-phone-box">
                                <div class="input-group mb-3 phone-detail">
                                    <select class="form-control select-country-code form-control-white d-block order-0"
                                        id="select-country-code" name="code" data-placeholder="1">
                                        @php
                                        $default = old('code', auth()?->user()?->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                                        @endphp
                                        <option value="" selected></option>
                                        @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                                        <option class="option" value="{{ $option->phone_code }}"
                                            data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                            @if($option->phone_code == $default) selected @endif
                                            data-default="{{ $default }}">
                                            +{{ $option->phone_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('code')
                                    <span class="invalid-feedback d-block d-block order-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror

                                    <input class="form-control form-control-white w-auto" type="number" name="phone"
                                        id="phone"
                                        value="{{ isset(auth()?->user()->phone) ? auth()?->user()->phone : old('phone') }}"
                                        min="1" placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                                </div>
                                @error('phone')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal"
                        aria-label="Close">{{ __('frontend::static.account.cancel') }}</button>
                    <button type="button" class="btn btn-solid"
                        id="submitProfileForm">{{ __('frontend::static.account.update_profile') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- update Change Password modal -->
<div class="modal fade profile-update-modal change-password-modal" id="change-password" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="personalDetailModalLabel">
                    {{ __('frontend::static.account.change_password') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('frontend.account.password.update') }}" id="changePasswordForm" method="post">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="currentPassword">{{ __('frontend::static.account.current_password') }}</label>
                        <input type="password" id="current_password" class="form-control"
                            placeholder="{{ __('frontend::static.account.enter_current_password') }}"
                            value="{{ old('current_password') }}" autocomplete="off" name="current_password">
                    </div>
                    @error('current_password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <div class="form-group">
                        <label for="password2">{{ __('frontend::static.account.new_password') }}</label>
                        <input class="form-control form-control-white" id="new_password"
                            placeholder="{{ __('frontend::static.account.enter_new_password') }}" autocomplete="off"
                            value="{{ old('new_password') }}" name="new_password" type="password">
                    </div>
                    @error('new_password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <div class="form-group">
                        <label for="confirmPassword">{{ __('frontend::static.account.confirm_password') }}</label>
                        <input class="form-control form-control-white" id="confirm_password"
                            placeholder="{{ __('frontend::static.account.enter_confirm_password') }}"
                            name="confirm_password" autocomplete="off" value="{{ old('confirm_password') }}"
                            type="password">
                    </div>
                    @error('confirm_password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal"
                        aria-label="Close">{{ __('frontend::static.account.cancel') }}</button>
                    <button type="submit"
                        class="btn btn-solid">{{ __('frontend::static.account.change_password') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
(function($) {
    "use strict";
    $(document).ready(function() {
        $("#changePasswordForm").validate({
            ignore: [],
            rules: {
                "current_password": "required",
                "new_password": {
                    required: true,
                    minlength:8
                },
                "confirm_password": {
                    required: true,
                    equalTo: "#new_password",
                    minlength:8
                },
            },
        });
    });
})(jQuery);
</script>
@endpush
