@extends('frontend.auth.master')
@section('content')
    <div class="log-in-section">
        <div class="row login-content g-0">
            <div class="login-main">
                <div class="login-card">
                    <div class="login-title">
                        <h2>{{ __('frontend::auth.verify_otp') }}</h2>
                        <p>{{ __('frontend::auth.enter_verification_code') }}
                            <span>+{{ $code }} {{ $phone }}</span>
                        </p>
                    </div>
                    <div class="login-detail phone-detail">
                        <form action="{{ route('frontend.login.otp.submit') }}" method="post" id="otpForm">
                            @csrf
                            <input type="hidden" name="code" value="{{ $code }}">
                            <input type="hidden" name="phone" value="{{ $phone }}">

                            <div class="form-group">
                                <label for="otp">{{ __('frontend::auth.enter_otp') }}</label>
                                <div class="otp-field">
                                    <input type="number" id="otp1" name="otp" class="otp__digit" maxlength="1">
                                    <input type="number" id="otp2" name="otp" class="otp__digit" maxlength="1">
                                    <input type="number" id="otp3" name="otp" class="otp__digit" maxlength="1">
                                    <input type="number" id="otp4" name="otp" class="otp__digit" maxlength="1">
                                    <input type="number" id="otp5" name="otp" class="otp__digit" maxlength="1">
                                    <input type="number" id="otp6" name="otp" class="otp__digit" maxlength="1">
                                </div>
                            </div>
                            <input type="hidden" name="otp" id="otp_hidden">
                            <button type="submit" class="otp-btn btn btn-solid">{{ __('frontend::auth.verify_proceed') }}</button>
                        </form>
                        <a href="{{route('frontend.login')}}" class="mt-2 d-block text-center">{{ __('Login Back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script src="{{ asset('frontend/js/otp.js') }}"></script>
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#otpForm").validate({
                ignore: [],
                rules: {
                    "otp": {
                        required: true,
                    },
                }
            });

            $('#otpForm').on('submit', function(event) {
                var otp1 = $('#otp1').val();
                var otp2 = $('#otp2').val();
                var otp3 = $('#otp3').val();
                var otp4 = $('#otp4').val();
                var otp5 = $('#otp5').val();
                var otp6 = $('#otp6').val();
                if (otp1 && otp2 && otp3 && otp4 && otp5 && otp6) {
                    var otpValue = otp1 + otp2 + otp3 + otp4 + otp5 + otp6;
                    $('#otp_hidden').val(otpValue);
                }
            });
        });
    })(jQuery);
</script>
@endpush
