@use('App\Models\Setting')
@use('app\Helpers\Helpers')

@php
    $settings = Setting::first()->values;
    $lang = Helpers::getLanguageByLocale(Session::get('locale', 'en'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $lang->is_rtl ? 'rtl' : 'ltr' }}"
    @isset($themeOptions['general']['theme_color']) style=" --primary-color:{{ $themeOptions['general']['theme_color'] }};" @endisset>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset($settings['general']['favicon']) ?? asset('admin/images/faviconIcon.png') }}"
        type="image/x-icon">
    <link rel="shortcut icon"
        href="{{ asset($settings['general']['favicon']) ?? asset('admin/images/faviconIcon.png') }}"
        type="image/x-icon">
    <title>@yield('title')</title>
    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100;0,9..40,200;0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;0,9..40,900;0,9..40,1000;1,9..40,100;1,9..40,200;1,9..40,300;1,9..40,400;1,9..40,500;1,9..40,600;1,9..40,700;1,9..40,800;1,9..40,900;1,9..40,1000&display=swap"
        rel="stylesheet">

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/bootstrap/bootstrap.css') }}">

    <!-- ICONSAX css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/iconsax/iconsax.css') }}">

    <!-- select2 css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/vendors/select2.css') }}">

    @vite(['public/frontend/scss/style.scss'])

</head>

<body class="notLoaded">
    <!-- Loader Section Start -->
    <div class="page-loader" id="loader">
        <div class="page-loader-wrapper">
            <img src="{{ asset('frontend/images/gif/loader.gif') }}" alt="loader">
        </div>
    </div>
    <!-- Loader Section End -->

    <div class="log-in-section">
        <div class="row login-content g-0">
            <div class="col image-col col-xl-6 d-xl-block d-none">
                <div class="image-contain">
                    <a href="{{ route('frontend.home') }}"><img
                            src="{{ asset($themeOptions['authentication']['header_logo']) ?? '' }}" class="logo"
                            alt=""></a>
                    <img src="{{ asset($themeOptions['authentication']['auth_images']) ?? '' }}" class="auth-image"
                        alt="">
                    <div class="auth-content">
                        <h2>{{ $themeOptions['authentication']['title'] }}</h2>
                        <p>
                            {{ $themeOptions['authentication']['description'] }}
                        </p>
                        <div class="app-install">
                            <a href="{{ $themeOptions['general']['app_store_url'] }}" target="_blank"
                                rel="noopener noreferrer">
                                <img src="{{ asset('frontend/images/app-store.png') }}" alt="app store">
                            </a>
                            <a href="{{ $themeOptions['general']['google_play_store_url'] }}" target="_blank"
                                rel="noopener noreferrer">
                                <img src="{{ asset('frontend/images/google-play.png') }}" alt="google play">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col content-col col-xl-6 col-12 p-0">
                <div class="login-main">
                    <div class="pt-4">
                        <div class="dropdown language-dropdown">
                            @php
                                $lang = Helpers::getLanguageByLocale(Session::get('locale', 'en'));
                                $flag = $lang?->flag;
                            @endphp

                            @if (count(Helpers::getLanguages()) <= 1)
                                <a href="{{ route('lang', ['locale' => @$lang?->locale, 'locale_flag' => @$lang?->flag_path]) }}"
                                    class="language-btn">
                                    <span>
                                        @if (Helpers::isFileExistsFromURL($flag))
                                            <img src="{{ Helpers::isFileExistsFromURL($flag, true) }}"
                                                alt="{{ $lang?->name }}" class="img-fluid">
                                        @else
                                            <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                                class="img-fluid">
                                        @endif

                                        {{ strtoupper(Session::get('locale', 'en')) }}
                                    </span>
                                </a>
                            @else
                                {{-- Multiple languages – show dropdown --}}
                                <button id="languageSelected" class="language-btn">
                                    <span>
                                        @if (Helpers::isFileExistsFromURL($flag))
                                            <img src="{{ Helpers::isFileExistsFromURL($flag, true) }}"
                                                alt="{{ $lang?->name }}" class="img-fluid">
                                        @else
                                            <img src="{{ asset('admin/images/No-image-found.jpg') }}" alt=""
                                                class="img-fluid">
                                        @endif

                                        {{ strtoupper(Session::get('locale', 'en')) }}
                                    </span>
                                    <i class="iconsax" icon-name="chevron-down"></i>
                                </button>
                                <ul class="onhover-show-div">
                                    @forelse (Helpers::getLanguages() as $lang)
                                        <li class="lang">
                                            <a href="{{ route('lang', ['locale' => @$lang?->locale, 'locale_flag' => @$lang?->flag_path]) }}"
                                                data-lng="{{ @$lang?->locale }}">
                                                <img class="img-fluid lang-img" alt="{{ $lang?->name }}"
                                                    src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}">
                                                {{ @$lang?->name }}
                                            </a>
                                        </li>
                                    @empty
                                        <li class="lang">
                                            <a href="{{ route('lang', 'en') }}" data-lng="en">
                                                <img class="active-icon" src="{{ asset('admin/images/flags/LR.png') }}"
                                                    alt="en"> En
                                            </a>
                                        </li>
                                    @endforelse
                                </ul>
                            @endif
                        </div>
                    </div>

                    <div class="login-card">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest jquery -->
    <script src="{{ asset('admin/js/jquery-3.7.1.min.js') }}"></script>

    <script src="{{ asset('frontend/js/jquery-ui.min.js') }}"></script>
    <!-- Bootstrap js -->
    <script src="{{ asset('frontend/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap/popper.min.js') }}"></script>
    <!-- Iconsax js -->
    <script src="{{ asset('frontend/js/iconsax/iconsax.js') }}"></script>
    <!-- Swiper-bundle js -->
    <script src="{{ asset('frontend/js/swiper-slider/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/js/swiper.js') }}"></script>
    <!-- Script js -->
    <script src="{{ asset('frontend/js/aos.js') }}"></script>
    <script src="{{ asset('frontend/js/custom-aos.js') }}"></script>
    <script src="{{ asset('frontend/js/script.js') }}"></script>

    <!-- Js Validator  -->
    <script src="{{ asset('admin/js/jquery-validation/jquery-validate.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/additional-methods.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/additional-methods.min.js') }}"></script>

    <!-- Password Hide Show Js -->
    <script src="{{ asset('frontend/js/password-hide-show.js') }}"></script>

    @stack('js')
    <script>
        $('form').on('submit', function(e) {
            var $form = $(this);
            var $submitButton = $form.find('.submit.spinner-btn');
            var $spinner = $submitButton.find('.spinner-border');
            e.preventDefault();
            if ($form.valid()) {
                if ($submitButton.length && $spinner.length) {
                    $spinner.show();
                    $submitButton.prop('disabled', true);
                }

                $form[0].submit();
            }
        });
    </script>
</body>

</html>
