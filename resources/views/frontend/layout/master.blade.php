@use('app\Helpers\Helpers')

@php

$lang = Helpers::getLanguageByLocale(Session::get('locale', 'en'));
$themeOptions = Helpers::getThemeOptions();

@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  dir="{{($lang->is_rtl) ? 'rtl' : 'ltr'}}" @isset($themeOptions['general']['theme_color']) style=" --primary-color:{{ $themeOptions['general']['theme_color'] }}; @isset($themeOptions['general']['font_color']) --font-color:{{ $themeOptions['general']['font_color'] }}; @endisset" @endisset>

<head>
    @include("frontend.layout.head")
</head>

<body class="notLoaded">
    <!-- Loader Section Start -->
    <div class="page-loader" id="loader">
        <div class="page-loader-wrapper">
            <img src="{{ asset('frontend/images/gif/loader.gif') }}" alt="loader">
        </div>
    </div>
    <!-- Loader Section End -->

    @include("frontend.layout.header")

    @hasSection('breadcrumb')
    <!-- Home Section Start -->
    <section class="breadcrumb-section ratio_18">
        <img src="{{asset('frontend/images/bg.jpg')}}" alt="bg" class="bg-img">
        <div class="container-fluid-lg">
            <div class="breadcrumb-contain">
                <div>
                    <h2><span>@yield('title')</span></h2>
                    <p>{{ $themeOptions['general']['breadcrumb_description'] }}</p>
                    @yield('breadcrumb')
                </div>
            </div>
        </div>
    </section>
    <!-- Home Section End -->
    @endif

    @yield('content')

    @include("frontend.layout.footer")

    @include("frontend.layout.script")
    @include("frontend.inc.alerts")
</body>
</html>
