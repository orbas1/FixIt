@use('app\Helpers\Helpers')
@extends('frontend.auth.master')
@section('content')
<div class="login-title">
    <h2>{{ __('frontend::auth.login_now') }}</h2>
    <p>{{ __('frontend::auth.title') }}</p>
</div>

<div class="login-detail mb-0">
    <form action="{{ route('frontend.login.number.otp') }}" method="POST" id="loginNumberForm">
        @csrf
        <div class="category-list-box">
            <label class="label-title" for="phone">{{ __('static.phone') }}<span class="required-span">*</span></label>
            <div class="w-100">
                <div class="input-group phone-detail">
                    <select class="select-2 form-control select-country-code" name="code" data-placeholder="">
                        @php
                        $default = old('code', auth()?->user()?->code ?? Helpers::getDefaultCountryCode());
                        @endphp
                        <option value="" selected></option>
                        @foreach (Helpers::getCountryCodes() as $key => $option)
                        <option class="option" value="{{ $option->phone_code }}"
                            data-image="{{ asset('admin/images/flags/' . $option->flag) }}" @if ($option->phone_code ==
                            $default) selected @endif data-default="{{ $default }}">
                            +{{ $option->phone_code }}
                        </option>
                        @endforeach
                    </select>
                    @error('code')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <input class="form-control form-control-white px-3" type="number" name="phone" id="phone"
                        value="{{ isset(auth()?->user()->phone) ? auth()?->user()->phone : old('phone') }}" min="1"
                        placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                    @error('phone')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-solid submit">
            {{ __('frontend::auth.login_now') }}
        </button>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('admin/js/select2.full.min.js') }}"></script>
<script>
(function($) {
    $(document).ready(function() {
        // Show All Country Flag beside Country Code in Select Box
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
@endpush
