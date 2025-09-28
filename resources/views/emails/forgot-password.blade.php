@component('mail::message')
    <h1>{{ __('auth.reset_password') }}</h1>
    {{ __('auth.code') }}
    <h2>{{ $otp }}</h2>
@endcomponent
