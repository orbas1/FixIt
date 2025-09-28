@extends('frontend.auth.master')

@section('content')
    <div class="login-title">
        <h2>{{ __('frontend::static.account.forgot') }}</h2>
        <p>{{ __('frontend::static.account.forgot_title') }}</p>
    </div>
    <div class="login-detail mb-0">
        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        @endif
        <form action="{{ route('frontend.forgot.otp') }}" method="POST" id="forgotForm">
            @csrf
            @method('POST')
            <label for="email">{{ __('frontend::static.account.email_or_phone') }}</label>
            <div class="form-group">
                <i class="iconsax" icon-name="mail"></i>
                <input class="form-control" id="email_or_phone" placeholder="{{ __('frontend::static.account.enter_email_or_phone_no') }}" name="email_or_phone" type="email_or_phone">
                    @error('email_or_phone')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-solid submit spinner-btn">
            {{ __('frontend::static.account.send_otp') }}<span class="spinner-border spinner-border-sm" style="display: none;"></span>
            </button>
            <div class="not-member">
                <span>Not Forgot Password</span>
                <a href="{{ route('frontend.login.index') }}">{{ __('frontend::auth.sign_in') }}</a>
            </div>
            
        </form>
    </div>
@endsection

@push('js')
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#forgotForm").validate({
                ignore: [],
                rules: {
                    "email_or_phone": {
                        required: true,
                    },
                }
            });
        });
    })(jQuery);
</script>
@endpush
