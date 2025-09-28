@extends('auth.master')

@section('title', __('Verify'))

@section('content')
<section class="auth-page">
    <div class="animation-circle-inverse"><i></i><i></i><i></i></div>
    <div class="animation-circle"><i></i><i></i><i></i></div>
    <div class="auth-card">
        <div class="text-center">
            <h2>{{ __('auth.verify_page_message') }}</h2>
        </div>
        <div class="main">
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    {{ __('auth.sent_verification_link_msg') }}
                </div>
            @endif
            {{ __('auth.check_verification_link_msg') }}
            {{ __('auth.not_recieved__email_msg') }},
            <form action="{{ route('verification.resend') }}" class="auth-form" method="post">
                <button type="submit" class="btn submit">{{ __('auth.request_another_code') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection
