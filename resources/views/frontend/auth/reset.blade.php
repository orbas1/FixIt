@extends('frontend.auth.master')
@section('content')
    <div class="login-title">
        <h2>{{ __('frontend::auth.reset_password') }}</h2>
        <p>{{ __('frontend::auth.reset_password_title') }}</p>
    </div>
    <div class="login-detail">
        <form action="{{ route('frontend.reset') }}" method="POST" id="resetForm">
            @csrf
            @method('POST')
            <input type="hidden" name="token" value="{{ $password_resets->token }}">
            <label for="email">{{ __('frontend::auth.email') }}</label>
            <div class="form-group">
                <div class="position-relative">
                    <i class="iconsax" icon-name="mail"></i>
                    <input class="form-control form-control-white" id="email" disabled value={{ $password_resets->email }}
                        name="email" type="email">
                </div>
                @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <label for="password">{{ __('frontend::auth.password') }}</label>
            <div class="form-group">
                <div class="position-relative">
                    <i class="iconsax" icon-name="lock-2"></i>
                    <input class="form-control form-control-white" id="password" placeholder="{{ __('frontend::auth.password') }}"
                        name="password" type="password">
                </div>
                <div class="toggle-password">
                    <i class="iconsax eye" icon-name="eye"></i>
                    <i class="iconsax eye-slash" icon-name="eye-slash"></i>
                </div>
                @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <label for="password1">{{ __('frontend::auth.confirm_password') }}</label>
            <div class="form-group mb-3">
                <div class="position-relative">
                    <i class="iconsax" icon-name="lock-2"></i>
                    <input class="form-control form-control-white" id="confirm_password" placeholder="Enter your password"
                        name="confirm_password" type="password">
                </div>
                <div class="toggle-password">
                    <i class="iconsax eye" icon-name="eye"></i>
                    <i class="iconsax eye-slash" icon-name="eye-slash"></i>
                </div>

                @error('confirm_password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-solid" data-bs-toggle="modal" data-bs-target="#resetModal">
            {{ __('frontend::auth.reset_password') }}
            </button>
        </form>
    </div>
@endsection

@push('js')
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#resetForm").validate({
                ignore: [],
                rules: {
                    "password": {
                        required: true
                    },
                    "confirm_password": {
                        required: true,
                        equalTo: "#password"
                    }
                }
            });
        });
    })(jQuery);
</script>
@endpush
