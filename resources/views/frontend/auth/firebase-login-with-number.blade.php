@use('app\Helpers\Helpers')
@extends('frontend.auth.master')

@section('content')
<link rel="stylesheet" rel="preload" type="text/css" href="{{ asset('frontend/css/vendors/toastr.min.css') }}">

<div class="login-title">
    <h2>{{ __('frontend::auth.login_now') }}</h2>
    <p>{{ __('frontend::auth.title') }}</p>
</div>
<div class="login-detail mb-0">
    <form action="" method="POST" id="loginNumberForm">
        @csrf
        <div class="category-list-box" id="phone-group">
            <label class="label-title" for="phone">{{ __('static.phone') }}<span class="required-span">*</span></label>
            <div class="w-100">
                <div class="input-group phone-detail" >
                    <select class="select-2 form-control select-country-code" name="code"  id="code" data-placeholder="">
                        @php
                            $default = old('code', auth()?->user()?->code ?? Helpers::getDefaultCountryCode());
                        @endphp
                        <option value="" selected></option>
                        @foreach (Helpers::getCountryCodes() as $key => $option)
                            <option class="option" value="{{ $option->phone_code }}" data-image="{{ asset('admin/images/flags/' . $option->flag) }}" @if ($option->phone_code == $default) selected @endif data-default="{{ $default }}">
                                +{{ $option->phone_code }}
                            </option>
                        @endforeach
                    </select>
                    @error('code')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <input class="form-control form-control-white px-3" type="number" name="phone" id="phone" value="{{ isset(auth()?->user()->phone) ? auth()?->user()->phone : old('phone') }}" min="1" placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                    @error('phone')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="input-group"  id="otp-code">
            <input class="form-control form-control-white" type="number" name="otp" id="verification-code" style="display:none" value="" min="1" placeholder="{{ __('static.serviceman.enter_otp') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
            @error('phone')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div id="recaptcha-container"></div>
        <button type="button" class="btn btn-solid submit spinner-btn" id="generate_otp"><span class="spinner-border spinner-border-sm" style="display: none;"></span>{{ __('frontend::auth.login_now') }}</button>
        <button type="button" class="btn btn-solid submit spinner-btn" id="verify_otp" style="display:none"><span class="spinner-border spinner-border-sm" style="display: none;"></span>Verify</button>
    </form>
</div>

@endsection

@push('js')
<script src="{{ asset('admin/js/select2.full.min.js') }}"></script>
<script src="{{ asset('frontend/js/toastr.min.js') }}"></script>

<script>
(function($) {
    $(document).ready(function() {
        var defaultCountryCode = $('.select-country-code option:selected').data('default');
        $('.select-country-code').select2({
            templateResult: function(data) {
                if (!data.id) {
                    return data.text;
                }
                var $result = $('<span><img src="' + $(data.element).data('image') +
                    '" class="flag-img" /> ' + data.text + '</span>');
                return $result;
            },
            templateSelection: function(selection) {
                if (selection.text == '') {
                    return selection.text;
                }
                return selection.id ? selection.text : '';
            }
        });
    });
    $("#loginNumberForm").validate({
        ignore: [],
        rules: {
            "code": {
                required: true,
            },
            "phone": {
                required: true
            },
        }
    });

})(jQuery);
</script>
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js";
    import { getAuth, RecaptchaVerifier, signInWithPhoneNumber, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-auth.js";

        var apiKey = "{{ env('FIREBASE_API_KEY') }}";
        var authDomain = "{{ env('FIREBASE_AUTH_DOMAIN') }}";
        var projectId = "{{ env('FIREBASE_PROJECT_ID') }}";
        var storageBucket = "{{ env('FIREBASE_STORAGE_BUCKET') }}";
        var messagingSenderId = "{{ env('FIREBASE_SENDER_ID') }}";
        var appId = "{{ env('FIREBASE_APP_ID') }}";
        var measurementId = "{{ env('FIREBASE_MEASUREMENT_ID') }}";

        const firebaseConfig = {
            apiKey: apiKey,
            authDomain: authDomain,
            projectId: projectId,
            storageBucket: storageBucket,
            messagingSenderId: messagingSenderId,
            appId: appId,
            measurementId: measurementId
            };


    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    let confirmationResult;
    let recaptchaVerifier;

    $(document).ready(function () {
    try {

        if (!$('#recaptcha-container').length) {
            $('body').append('<div id="recaptcha-container"></div>');
        }

        recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
            'size': 'invisible',
            'callback': function(response) {
                console.log('reCAPTCHA solved:', response);
            }
        });

        $('#generate_otp').click(function () {
            let phoneNumber = $('#phone').val();
            const countryCode = $('#code').val();

            if (!phoneNumber) {
                toastr.error("Please enter a phone number");
                return;
            }

            $(this).prop('disabled', true);
            $(this).append('<span class="spinner"></span>');

            phoneNumber = `+${countryCode}${phoneNumber}`;

            signInWithPhoneNumber(auth, phoneNumber, recaptchaVerifier)
                .then((result) => {
                    confirmationResult = result;
                    toastr.success("OTP sent successfully!");
                    $('#generate_otp').hide();
                    $('#verify_otp').show();
                    $('#verification-code').show();
                    $('#phone-group').hide();
                })
                .catch((error) => {
                    console.error('Error during sign-in:', error);
                    toastr.error('Error: ' + error.message);
                })
                .finally(() => {
                    $('#generate_otp').prop('disabled', false).find('.spinner').remove();
                });
        });

        $('#verify_otp').click(function () {
            const code = $('#verification-code').val();

            if (!confirmationResult) {
                toastr.error("Please request an OTP first");
                return;
            }

            $(this).prop('disabled', true).append('<span class="spinner ms-2"></span>');

            confirmationResult.confirm(code)
                .then((result) => {
                    const user = result.user;
                    var phoneNum = $('#phone').val();

                    var url = "{{ route('frontend.firebase.verify-otp') }}";
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            phone: phoneNum,
                            code: code,
                            firebase_uid: user.uid
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success("Verification successful!");
                                window.location.href = response.redirect;
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON.message);
                        }
                    });
                })
                .catch((error) => {
                    console.error('Error during verification:', error);
                    toastr.error('Verification failed. Please try again.');
                })
                .finally(() => {
                    $('#verify_otp').prop('disabled', false).find('.spinner').remove();
                });
        });

    } catch (error) {
        console.error('Initialization error:', error);
    }
});
</script>
@endpush

