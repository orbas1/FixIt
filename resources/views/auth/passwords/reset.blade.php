@extends('auth.master')
@section('title', __('auth.reset_password'))
@section('content')
@php
    $settings = \App\Models\Setting::first()->values;
@endphp
<section class="auth-page" style="background-image: url('{{ env('APP_URL') }}/admin/images/login-bg.png')">
    <div class="container">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-lg-8 ms-auto">
                <div class="auth-card">
                    <div class="text-center">
                        <img class="login-img" src="{{ asset($settings['general']['dark_logo']) ?? asset('admin/images/logo-dark.png') }}">
                    </div>
                    <div class="welcome">
                        <h3>{{ __('auth.reset_password') }}</h3>
                        <p>{{ __('auth.reset_Password_message') }}</p>
                    </div>
                    <div class="main">
                        <form action="{{ route('password.update') }}" method="POST">
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="form-group">
                            <label for="email">{{ __('static.email') }}</label><i data-feather="mail"></i>
                            <input class="form-control search-input" type="email" name="email" id="email" value="{{ $email ?? old('email') }}" placeholder="{{ __('static.e-mail_address') }}" disabled>
                            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">{{ __('static.password') }}</label>
                            <i data-feather="lock"></i>
                            <input type="password" class="form-control search-input" name="password" id="password" placeholder="{{ __('static.users.enter_password') }}">
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">{{ __('static.password') }}</label>
                            <i data-feather="lock"></i>
                            <input type="password" class="form-control search-input" name="password_confirmation" id="password_confirmation" placeholder="{{ __('static.users.re_enter_password') }}">
                            @error('password_confirmation')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn submit">{{ __('auth.reset_password') }}</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="animate-object">
            <img class="vase-img" src="{{ asset('admin/images/vase.png') }}">
            <img class="girl-img" src="{{ asset('admin/images/girl.png') }}">
            <img class="lamp-img" src="{{ asset('admin/images/lamp.png') }}">
            <div class="clockbox">
                <svg id="clock" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
                    <g id="face">
                        <circle class="circle" cx="300" cy="300" r="253.9"></circle>
                        <path class="hour-marks"
                            d="M300.5 94V61M506 300.5h32M300.5 506v33M94 300.5H60M411.3 107.8l7.9-13.8M493 190.2l13-7.4M492.1 411.4l16.5 9.5M411 492.3l8.9 15.3M189 492.3l-9.2 15.9M107.7 411L93 419.5M107.5 189.3l-17.1-9.9M188.1 108.2l-9-15.6">
                        </path>
                        <circle class="mid-circle" cx="300" cy="300" r="16.2"></circle>
                    </g>
                    <g id="hour">
                        <path class="hour-hand" d="M300.5 298V142"></path>
                        <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                    </g>
                    <g id="minute">
                        <path class="minute-hand" d="M300.5 298V67"> </path>
                        <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                    </g>
                    <g id="second">
                        <path class="second-hand" d="M300.5 350V55"></path>
                        <circle class="sizing-box" cx="300" cy="300" r="253.9"> </circle>
                    </g>
                </svg>
            </div>
        </div>
    </div>
</section>
@endsection
@push('js')
<script>
$("#loginForm").validate({
    ignore: [],
    rules: {
        "password": {
            required: true
        },
        "confirm_password": {
            required: true,
            equalTo: "#new_password"
        },
    }
});
</script>
@endpush
