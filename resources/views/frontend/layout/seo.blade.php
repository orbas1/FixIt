{{-- Title --}}
<title>@yield('title', $themeOptions['general']['site_title'] ?? $themeOptions['seo']['meta_title']) - {{$themeOptions['general']['site_tagline'] ?? null}}</title>

{{-- Meta Tags --}}
<meta name="title" content="{{ $themeOptions['seo']['meta_title'] ?? env('APP_NAME') }}">
<meta name="description" content="@yield('meta_description', $themeOptions['seo']['meta_description'])">
<meta name="keywords" content="@yield('keywords', $themeOptions['seo']['meta_tags'])">
<meta name="robots" content="index, follow"> <!-- or change to noindex, nofollow as needed -->

{{-- Canonical URL --}}
<link rel="canonical" href="@yield('canonical_url', url()->current())">

{{-- Open Graph Tags --}}
<meta property="og:title" content="@yield('og_title', $themeOptions['seo']['og_title'] ?? $themeOptions['general']['site_title'])">
<meta property="og:description" content="@yield('og_description', $themeOptions['seo']['og_description'])">
<meta property="og:image" content="@yield('og_image', asset($themeOptions['seo']['og_image'] ?? $themeOptions['general']['header_logo']))">
<meta property="og:url" content="@yield('og_url', url()->current())">
<meta property="og:type" content="website">

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('twitter_title', $themeOptions['seo']['og_title'] ?? $themeOptions['general']['site_title'])">
<meta name="twitter:description" content="@yield('twitter_description', $themeOptions['seo']['og_description'])">
<meta name="twitter:image" content="@yield('twitter_image', asset($themeOptions['seo']['og_image'] ?? $themeOptions['general']['header_logo']))">

{{-- Additional Meta Tags --}}
<meta property="og:site_name" content="{{ $themeOptions['general']['site_title'] }}">
<meta property="og:locale" content="en_US">

{{-- Viewport --}}
<meta name="viewport" content="width=device-width, initial-scale=1.0">

{{-- Favicon --}}
<link rel="icon" href="{{ asset(@$themeOptions['general']['favicon_icon'] ?? asset('admin/images/faviconIcon.png')) }}" type="image/x-icon">

{{-- Verification Code (if applicable) --}}
<meta name="msvalidate.01" content="your-verification-code" />