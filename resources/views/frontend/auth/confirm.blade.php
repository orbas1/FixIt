@extends('frontend.auth.master')
@section('content')
    <div class="login-title">
        <h2>{{ __('frontend::static.account.verify_otp') }}</h2>
        <p>{{ __('frontend::static.account.cofirm_title') }}
            <span>“{{ session('email_or_phone') }}”</span>
        </p>
    </div>
    <img src="{{ asset('frontend/images/auth/otp.png') }}" alt="forgot" class="login-img">
    <div class="login-detail">
        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        @endif
        <form action="{{ route('frontend.confirm', $token) }}" method="POST" id="confirmForm">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="email">{{ __('frontend::static.account.enter_otp') }}</label>
                <div class="otp-field">
                    <input type="number" id="otp1" name="otp1" class="otp__digit otp__field__1">
                    <input type="number" id="otp2" name="otp2" class="otp__digit otp__field__2">
                    <input type="number" id="otp3" name="otp3" class="otp__digit otp__field__3">
                    <input type="number" id="otp4" name="otp4" class="otp__digit otp__field__4">
                    <input type="number" id="otp5" name="otp5" class="otp__digit otp__field__5">
                    <input type="number" id="otp6" name="otp6" class="otp__digit otp__field__6">
                </div>
                <input type="hidden" name="otp" id="otp_hidden">
            </div>
            <button type="submit" class="btn btn-solid submit">{{ __('frontend::static.account.verify_proceed') }}</button>
        </form>
    </div>
@endsection

@push('js')
<script src="{{ asset('frontend/js/otp.js') }}"></script>
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#confirmForm").validate({
                ignore: [],
                rules: {
                    "otp": {
                        required: true,
                    },
                }
            });

            $('#confirmForm').on('submit', function(event) {
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
