@extends('frontend.auth.master')

@section('content')
<div class="login-title">
    <h2>{{ __('frontend::auth.register_now') }}</h2>
    <p>{{ __('frontend::auth.title') }}</p>
</div>
<div class="login-detail">
    @if (session()->has('error'))
    <div class="alert alert-danger">
        {{ session()->get('error') }}
    </div>
    @endif
    <form action="{{ route('frontend.register') }}" method="POST" id="registerForm">
        @csrf
        @method('POST')
        <div class="form-group">
            <i class="iconsax" icon-name="user"></i>
            <label for="name">{{ __('frontend::auth.name') }}</label>
            <div class="position-relative">
                <i class="iconsax" icon-name="user-1"></i>
                <input class="form-control form-control-white" id="name" placeholder="{{ __('frontend::auth.enter_name') }}"
                    name="name" type="name" value="{{ old('name') }}">
            </div>
            @error('name')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">{{ __('frontend::auth.email') }}</label>
            <div class="position-relative">
                <i class="iconsax" icon-name="mail"></i>
                <input class="form-control form-control-white" id="email"
                    placeholder="{{ __('frontend::auth.enter_email') }}" name="email" type="email" value="{{ old('email') }}">
            </div>
            @error('email')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">{{ __('frontend::auth.password') }}</label>
            <div class="position-relative">
                <i class="iconsax" icon-name="lock-2"></i>
                <input class="form-control form-control-white pr-45" id="password"
                    placeholder="{{ __('frontend::auth.enter_your_password') }}" name="password" type="password">
                <div class="toggle-password">
                    <i class="iconsax eye" icon-name="eye"></i>
                    <i class="iconsax eye-slash" icon-name="eye-slash"></i>
                </div>
            </div>
            @error('password')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="password-input">{{ __('frontend::auth.confirm_password') }}</label>
            <div class="position-relative">
                <i class="iconsax" icon-name="lock-2"></i>
                <input class="form-control form-control-white pr-45" id="password_confirmation"
                    placeholder="{{ __('frontend::auth.enter_confirm_password') }}" name="password_confirmation"
                    type="password">
                <div class="toggle-password">
                    <i class="iconsax eye" icon-name="eye"></i>
                    <i class="iconsax eye-slash" icon-name="eye-slash"></i>
                </div>
            </div>
            @error('password_confirmation')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="terms pb-0">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="agree" name="agree">
                <a href="#terms" data-bs-toggle="modal">
                    <p>{{ __('frontend::auth.agree_terms') }}</p>
                </a>
            </div>
            @error('agree')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <button type="submit" class="btn btn-solid submit spinner-btn">
            {{ __('frontend::auth.submit') }} <span class="spinner-border spinner-border-sm"
                style="display: none;"></span>
        </button>
        <div class="not-member">
            <span>{{ __('frontend::auth.already_member') }}</span>
            <a href="{{ route('frontend.login.index') }}">{{ __('frontend::auth.sign_in') }}</a>
        </div>
    </form>
</div>

<div class="modal terms-modal  fade" id="terms" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content section-bg">
            <div class="modal-header">
                <h3 class="modal-title fs-5">Terms and Conditions</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body custom-scroll">
                <div class="accordion" id="privacyPolicyExample">
                    @forelse ($themeOptions['terms_and_conditions']['banners'] ?? [] as $key => $banners)
                    <div class="accordion-item">
                        <h2 class="accordion-header">

                            @isset($banners['title'])
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#privacyPolicyCollapse{{ $key }}" aria-expanded="false"
                                aria-controls="privacyPolicyCollapse{{ $key }}">
                                {{ $banners['title'] }}
                                <i class="iconsax add" icon-name="add"></i>
                                <i class="iconsax minus" icon-name="minus"></i>
                            </button>
                            @endisset
                        </h2>
                        <div id="privacyPolicyCollapse{{ $key }}" class="accordion-collapse collapse"
                            data-bs-parent="#privacyPolicyExample">
                            <div class="accordion-body">
                                @isset($banners['description'])
                                {!! $banners['description'] !!}
                                @endisset
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="no-data-found bg-white">
                             <svg class="no-data-img">
                                <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                            </svg>
                            <p>{{__('frontend::static.terms.data_not_found')}}</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function($) {
    "use strict";
    $(document).ready(function() {
        $("#registerForm").validate({
            ignore: [],
            rules: {
                "name": {
                    required: true
                },
                "email": {
                    required: true,
                    email: true
                },
                "password": {
                    required: true
                },
                "password_confirmation": {
                    required: true,
                    equalTo: "#password"
                },
                "agree": {
                    required: true
                },
            },
        });
    });
})(jQuery);
</script>
@endpush