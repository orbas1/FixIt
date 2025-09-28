@extends('auth.master')
@section('title', __('auth.confirm_password'))
@section('content')
    <section class="auth-page" style="background-image: url('{{ env('APP_URL') }}/admin/images/login-bg.png')">
        <div class="animation-circle-inverse"><i></i><i></i><i></i></div>
        <div class="animation-circle"><i></i><i></i><i></i></div>
        <div class="auth-card">
            <div class="text-center">
                <h2>{{ __('auth.confirm_password') }}</h2>
                <div class="line"></div>
            </div>
            <div class="main">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <form id="confirmForm" class="auth-form" action="{{route('password.confirm')}}" method="POST">
                    <div class="form-group">
                        <label for="password">{{ __('static.password') }}</label>
                        <i class="fa fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('static.password') }}">
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="btn btn-default forgot-pass">{{ __('static.forgot') }}</a>
                        @endif
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn submit">{{ __('auth.confirm_password') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
