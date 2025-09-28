@use('App\Helpers\Helpers')
@use('App\Models\Zone')
@use('App\Models\Language')
@use('App\Models\Setting')
@php
$zones = Zone::where('status', true)->pluck('name', 'id');
$countries = Helpers::getCountries();
$languages = Language::get();
$settings = Setting::first()->values;
@endphp

@extends('auth.master')

@section('title', __('Register'))

@section('content')
<section class="auth-page" style="background-image: url('{{ env('APP_URL') }}/admin/images/login-bg.png')">
    <div class="container">
         <div class="auth-card">
            <div class="welcome mt-0">
                <h3>{{ __('Become a Provider') }}</h3>
                <p>{{ __('static.sign_in_note') }}</p>
            </div>
            @if ($errors->any())
            <div class="error-note" id="errors">
                @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
                <i data-feather="x" class="close-errors"></i>
            </div>
            @endif
            <div class="main">
                <form class="auth-form" action="{{ route('become-provider.store') }}" id="providerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('auth.become-provider.fields')
                </form>
                <div class="forgot-pass">
                    @if (Route::has('login'))                                
                        <a href="{{ route('login') }}" class="btn ">
                            <i data-feather="arrow-left"></i>
                            {{ __('static.login.back_to_login') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection


@push('js')
<script>
$(document).ready(function() {
    $(".close-errors").click(function() {
        $("#errors").remove();
    });

    $("#loginForm").validate({
        ignore: [],
        rules: {
            "email": "required",
            "password": "required",
        }
    });

    $(".default-credentials").click(function() {
        $("#email-input").val("");
        $("#password-input").val("");
        var email = $(this).data("email");
        $("#email-input").val(email);
        $("#password-input").val("123456789");
    });
});
</script>
@endpush