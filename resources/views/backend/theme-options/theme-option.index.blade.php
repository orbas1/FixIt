@extends('backend.layouts.master')

@section('title', __('static.theme_options.theme_options'))

@section('content')
    @use('App\Models\Settings')
    @use('app\Helpers\Helpers')

    @php
        $providers = Helpers::getProviders()?->pluck('name', 'id');
    @endphp

    <div class="card tab2-card">
        <div class="card-header">
            <h5>{{ __('static.theme_options.theme_options') }}</h5>
        </div>
        <div class="card-body">
            <div class="vertical-tabs">
                <div class="row g-xl-4 g-3">
                    <div class="col-xxl-3 col-xl-4 col-12">
                        <div class="nav nav-pills" id="v-pills-tab">
                            <a class="nav-link active" id="v-pills-tabContent" data-bs-toggle="pill" href="#general_option">
                                <i class="ri-settings-line"></i>{{ __('static.theme_options.general') }}
                            </a>

                            <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#header_option">
                                <i class="ri-layout-top-line"></i>{{ __('static.theme_options.header') }}
                            </a>

                            <a class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" href="#footer_option">
                                <i class="ri-layout-bottom-line"></i>{{ __('static.theme_options.footer') }}
                            </a>

                            <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#contact_us_option">
                                <i class="ri-contacts-line"></i>{{ __('static.theme_options.contact_us') }}
                            </a>

                            <a class="nav-link" id="v-pills-pagination-tab" data-bs-toggle="pill" href="#pagination_option">
                                <i class="ri-stack-fill"></i>{{ __('static.theme_options.paginations') }}
                            </a>

                            <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#about_us_option">
                                <i data-feather="user"></i>{{ __('static.theme_options.about_us') }}
                            </a>

                            <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#page">
                                <i class="ri-layout-horizontal-line"></i>{{ __('static.theme_options.pages') }}
                            </a>

                            <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#authentication">
                                <i class="ri-user-line"></i>{{ __('static.theme_options.auth') }}
                            </a>

                            <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#seoSection">
                                <i class="ri-earth-line"></i>{{ __('static.theme_options.seo') }}
                            </a>
                        </div>
                    </div>
                    <div class="col-xxl-7 col-xl-8 col-12">
                        <form method="POST" class="needs-validation user-add h-100" id="themeOptionForm"
                            action="{{ route('backend.update.theme_options', $themeOptionId) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="tab-content w-100" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="general_option">
                                    <div>
                                        <div class="form-group row">
                                            <label for="image"
                                                class="col-md-2">{{ __('static.theme_options.header_logo') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="file" id="general[header_logo]"
                                                    name="general[header_logo]">
                                                @error('general[header_logo]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <span
                                                    class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                            </div>
                                        </div>

                                        @isset($themeOptions['general']['header_logo'])
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <div class="image-list">
                                                            <div class="image-list-detail">
                                                                <div class="position-relative">
                                                                    <img src="{{ asset($themeOptions['general']['header_logo']) }}"
                                                                        id="{{ $themeOptions['general']['header_logo'] }}"
                                                                        alt="Header Logo" class="image-list-item">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endisset

                                        <div class="form-group row">
                                            <label for="image"
                                                class="col-md-2">{{ __('static.theme_options.favicon_icon') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="file" id="general[favicon_icon]"
                                                    name="general[favicon_icon]">
                                                @error('general[favicon_icon]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <span
                                                    class="help-text">{{ __('static.theme_options.upload_favicon_image_size') }}</span>
                                            </div>
                                        </div>

                                        @isset($themeOptions['general']['favicon_icon'])
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <div class="image-list">
                                                            <div class="image-list-detail">
                                                                <div class="position-relative">
                                                                    <img src="{{ asset($themeOptions['general']['favicon_icon']) }}"
                                                                        id="{{ $themeOptions['general']['favicon_icon'] }}"
                                                                        alt="favicon" class="image-list-item">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endisset

                                        <div class="form-group row">
                                            <label for="image"
                                                class="col-md-2">{{ __('static.theme_options.footer_logo') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="file" id="general[footer_logo]"
                                                    name="general[footer_logo]">
                                                @error('general[footer_logo]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <span
                                                    class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                            </div>
                                        </div>

                                        @isset($themeOptions['general']['footer_logo'])
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-10">
                                                        <div class="image-list">
                                                            <div class="image-list-detail">
                                                                <div class="position-relative">
                                                                    <img src="{{ asset($themeOptions['general']['footer_logo']) }}"
                                                                        id="{{ $themeOptions['general']['footer_logo'] }}"
                                                                        alt="Header Logo" class="image-list-item">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endisset

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="general[theme_color]">{{ __('static.theme_color') }}</label>
                                            <div class="col-md-10">
                                                <div class="d-flex align-items-center gap-2">
                                                    <input class="form-control primary-color" type="color"
                                                        name="general[theme_color]" id="general[theme_color]"
                                                        value="{{ isset($themeOptions['general']['theme_color']) ? $themeOptions['general']['theme_color'] : old('general[theme_color]') }}"
                                                        placeholder="{{ __('static.service_package.enter_color') }}">
                                                    <span class="color-picker">#000</span>
                                                </div>
                                                @error('theme_color')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="site_name">{{ __('static.theme_options.meta_title') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="text" id="general[site_title]"
                                                    name="general[site_title]"
                                                    value="{{ $themeOptions['general']['site_title'] ?? old('site_title') }}"
                                                    placeholder="{{ __('static.theme_options.enter_site_title') }}">
                                                @error('general[site_title]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="site_tagline">{{ __('static.theme_options.site_tagline') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="text" id="general[site_tagline]"
                                                    name="general[site_tagline]"
                                                    value="{{ $themeOptions['general']['site_tagline'] ?? old('site_tagline') }}"
                                                    placeholder="{{ __('static.theme_options.enter_site_tagline') }}">
                                                @error('general[site_tagline]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="breadcrumb_description"
                                                class="col-md-2">{{ __('Breadcrumb Description') }}</label>
                                            <div class="col-md-10">
                                                <textarea class="form-control" name="general[breadcrumb_description]" id="general[breadcrumb_description]"
                                                    placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['general']['breadcrumb_description'] ?? old('general[breadcrumb_description]') }}</textarea>
                                                @error('general[breadcrumb_description]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="general[app_store_url]">{{ __('App Store link') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="text" id="general[app_store_url]"
                                                    name="general[app_store_url]"
                                                    value="{{ $themeOptions['general']['app_store_url'] ?? old('general[app_store_url]') }}"
                                                    placeholder="{{ __('Enter App Store link') }}">
                                                @error('general[app_store_url]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="general[google_play_store_url]">{{ __('Google play store link') }}</label>
                                            <div class="col-md-10">
                                                <input class="form-control" type="text"
                                                    id="general[google_play_store_url]"
                                                    name="general[google_play_store_url]"
                                                    value="{{ $themeOptions['general']['google_play_store_url'] ?? old('general[google_play_store_url]') }}"
                                                    placeholder="{{ __('Enter google play store link') }}">
                                                @error('general[google_play_store_url]')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="header_option">
                                    <div>
                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="header[home]">{{ __('static.theme_options.home') }}</label>
                                            <div class="col-md-10">
                                                <div class="editor-space">
                                                    <label class="switch">
                                                        @if (isset($themeOptions['header']['home']))
                                                            <input class="form-control" type="hidden"
                                                                name="header[home]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[home]" value="1"
                                                                {{ $themeOptions['header']['home'] ? 'checked' : '' }}>
                                                        @else
                                                            <input class="form-control" type="hidden"
                                                                name="header[home]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[home]" value="1">
                                                        @endif
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="header[categories]">{{ __('static.theme_options.categories') }}</label>
                                            <div class="col-md-10">
                                                <div class="editor-space">
                                                    <label class="switch">
                                                        @if (isset($themeOptions['header']['categories']))
                                                            <input class="form-control" type="hidden"
                                                                name="header[categories]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[categories]" value="1"
                                                                {{ $themeOptions['header']['categories'] ? 'checked' : '' }}>
                                                        @else
                                                            <input class="form-control" type="hidden"
                                                                name="header[categories]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[categories]" value="1">
                                                        @endif
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="header[services]">{{ __('static.theme_options.services') }}</label>
                                            <div class="col-md-10">
                                                <div class="editor-space">
                                                    <label class="switch">
                                                        @if (isset($themeOptions['header']['services']))
                                                            <input class="form-control" type="hidden"
                                                                name="header[services]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[services]" value="1"
                                                                {{ $themeOptions['header']['services'] ? 'checked' : '' }}>
                                                        @else
                                                            <input class="form-control" type="hidden"
                                                                name="header[services]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[services]" value="1">
                                                        @endif
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="header[booking]">{{ __('static.theme_options.booking') }}</label>
                                            <div class="col-md-10">
                                                <div class="editor-space">
                                                    <label class="switch">
                                                        @if (isset($themeOptions['header']['booking']))
                                                            <input class="form-control" type="hidden"
                                                                name="header[booking]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[booking]" value="1"
                                                                {{ $themeOptions['header']['booking'] ? 'checked' : '' }}>
                                                        @else
                                                            <input class="form-control" type="hidden"
                                                                name="header[booking]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[booking]" value="1">
                                                        @endif
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-md-2"
                                                for="header[blogs]">{{ __('static.theme_options.blogs') }}</label>
                                            <div class="col-md-10">
                                                <div class="editor-space">
                                                    <label class="switch">
                                                        @if (isset($themeOptions['header']['blogs']))
                                                            <input class="form-control" type="hidden"
                                                                name="header[blogs]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[blogs]" value="1"
                                                                {{ $themeOptions['header']['blogs'] ? 'checked' : '' }}>
                                                        @else
                                                            <input class="form-control" type="hidden"
                                                                name="header[blogs]" value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="header[blogs]" value="1">
                                                        @endif
                                                        <span class="switch-state"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="footer_option">
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="footer_copyright">{{ __('static.theme_options.footer_copyright') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="footer[footer_copyright]"
                                                name="footer[footer_copyright]"
                                                value="{{ $themeOptions['footer']['footer_copyright'] ?? old('footer_copyright') }}"
                                                placeholder="{{ __('static.theme_options.enter_footer_copyright') }}">
                                            @error('footer[footer_copyright]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="btn_link">{{ __('static.theme_options.useful_link') }}</label>
                                        <div class="col-md-10">
                                            <select class="form-control select-2" id="useful_links"
                                                name="footer[useful_link][]"
                                                data-placeholder="{{ __('static.theme_options.select_useful_link') }}"
                                                multiple>
                                                <option class="select-placeholder" value=""></option>
                                                @php
                                                    $useful_links = Helpers::getFooterUsefulLinks();
                                                    $slugs = array_column(
                                                        @$themeOptions['footer']['useful_link'],
                                                        'slug',
                                                    );
                                                @endphp

                                                @foreach ($useful_links as $link)
                                                    <option value="{{ $link['slug'] }}"
                                                        @if (in_array($link['slug'], @$slugs ?? [])) selected @endif>
                                                        {{ $link['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="btn_link">{{ __('static.theme_options.pages') }}</label>
                                        <div class="col-md-10">
                                            <select class="form-control select-2" id="pages" name="footer[pages][]"
                                                data-placeholder="{{ __('static.theme_options.select_pages') }}" multiple>
                                                <option class="select-placeholder" value=""></option>
                                                @php
                                                    $pages = Helpers::getFooterPagesLinks();
                                                    $slugs = array_column(@$themeOptions['footer']['pages'], 'slug');
                                                @endphp

                                                @foreach ($pages as $page)
                                                    <option value="{{ $page['slug'] }}"
                                                        @if (in_array($page['slug'], @$slugs ?? [])) selected @endif>
                                                        {{ $page['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="btn_link">{{ __('static.theme_options.others') }}</label>
                                        <div class="col-md-10">
                                            <select class="form-control select-2" id="others" name="footer[others][]"
                                                data-placeholder="{{ __('static.theme_options.select_others') }}"
                                                multiple>
                                                <option class="select-placeholder" value=""></option>
                                                @php
                                                    $others = Helpers::getFooterOthersLinks();
                                                    $slugs = array_column(@$themeOptions['footer']['others'], 'slug');
                                                @endphp

                                                @foreach ($others as $other)
                                                    <option value="{{ $other['slug'] }}"
                                                        @if (in_array($other['slug'], @$slugs ?? [])) selected @endif>
                                                        {{ $other['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="footer[become_a_provider]">{{ __('static.theme_options.become_a_provider_enable') }}</label>
                                        <div class="col-md-10">
                                            <div class="editor-space">
                                                <label class="switch">
                                                    @if (isset($themeOptions['footer']['become_a_provider']))
                                                        <input class="form-control" type="hidden"
                                                            name="footer[become_a_provider][become_a_provider_enable]"
                                                            value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="footer[become_a_provider][become_a_provider_enable]"
                                                            value="1"
                                                            {{ $themeOptions['footer']['become_a_provider']['become_a_provider_enable'] ? 'checked' : '' }}>
                                                    @else
                                                        <input class="form-control" type="hidden"
                                                            name="footer[become_a_provider][become_a_provider_enable]"
                                                            value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="footer[become_a_provider][become_a_provider_enable]"
                                                            value="1">
                                                    @endif
                                                    <span class="switch-state"></span>
                                                </label>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="description">{{ __('static.theme_options.description') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text"
                                                id="footer[become_a_provider][description]"
                                                name="footer[become_a_provider][description]"
                                                value="{{ $themeOptions['footer']['become_a_provider']['description'] ?? old('footer_copyright') }}"
                                                placeholder="{{ __('static.theme_options.enter_description') }}">
                                            @error('footer[become_a_provider][description]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="contact_us_option">
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="header_title">{{ __('static.theme_options.header_title') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="contact_us[header_title]"
                                                name="contact_us[header_title]"
                                                value="{{ $themeOptions['contact_us']['header_title'] ?? old('contact_us[header_title]') }}"
                                                placeholder="{{ __('static.theme_options.enter_description') }}">
                                            @error('contact_us[header_title]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="title">{{ __('static.theme_options.title') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="contact_us[title]"
                                                name="contact_us[title]"
                                                value="{{ $themeOptions['contact_us']['title'] ?? old('contact_us[title]') }}"
                                                placeholder="{{ __('static.theme_options.enter_description') }}">
                                            @error('contact_us[title]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="description">{{ __('static.theme_options.description') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="contact_us[description]"
                                                name="contact_us[description]"
                                                value="{{ $themeOptions['contact_us']['description'] ?? old('contact_us[description]') }}"
                                                placeholder="{{ __('static.theme_options.enter_description') }}">
                                            @error('contact_us[description]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="email">{{ __('static.theme_options.email') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="contact_us[email]"
                                                name="contact_us[email]"
                                                value="{{ $themeOptions['contact_us']['email'] ?? old('contact_us[email]') }}"
                                                placeholder="{{ __('static.theme_options.enter_email') }}">
                                            @error('contact_us[email]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="contact">{{ __('static.theme_options.contact') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="contact_us[contact]"
                                                name="contact_us[contact]"
                                                value="{{ $themeOptions['contact_us']['contact'] ?? old('contact_us[contact]') }}"
                                                placeholder="{{ __('static.theme_options.enter_contact') }}">
                                            @error('contact_us[contact]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="location">{{ __('static.theme_options.location') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="contact_us[location]"
                                                name="contact_us[location]"
                                                value="{{ $themeOptions['contact_us']['location'] ?? old('contact_us[location]') }}"
                                                placeholder="{{ __('static.theme_options.enter_location') }}">
                                            @error('contact_us[location]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="google_map_embed_url">{{ __('static.theme_options.google_map_embed_url') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text"
                                                id="contact_us[google_map_embed_url]"
                                                name="contact_us[google_map_embed_url]"
                                                value="{{ $themeOptions['contact_us']['google_map_embed_url'] ?? old('contact_us[google_map_embed_url]') }}"
                                                placeholder="{{ __('static.theme_options.enter_google_map_embed_url') }}">
                                            @error('contact_us[google_map_embed_url]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pagination_option">
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[provider_per_page]">{{ __('static.theme_options.provider_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" id="pagination[provider_per_page]"
                                                name="pagination[provider_per_page]"
                                                value="{{ $themeOptions['pagination']['provider_per_page'] ?? old('provider_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[provider_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[blog_per_page]">{{ __('static.theme_options.blog_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" id="pagination[blog_per_page]"
                                                name="pagination[blog_per_page]"
                                                value="{{ $themeOptions['pagination']['blog_per_page'] ?? old('blog_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[blog_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[service_per_page]">{{ __('static.theme_options.service_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" id="pagination[service_per_page]"
                                                name="pagination[service_per_page]"
                                                value="{{ $themeOptions['pagination']['service_per_page'] ?? old('service_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[service_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[service_list_per_page]">{{ __('static.theme_options.service_list_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number"
                                                id="pagination[service_list_per_page]"
                                                name="pagination[service_list_per_page]"
                                                value="{{ $themeOptions['pagination']['service_list_per_page'] ?? old('service_list_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[service_list_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[service_package_per_page]">{{ __('static.theme_options.service_package_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number"
                                                id="pagination[service_package_per_page]"
                                                name="pagination[service_package_per_page]"
                                                value="{{ $themeOptions['pagination']['service_package_per_page'] ?? old('service_package_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[service_package_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[categories_per_page]">{{ __('static.theme_options.categories_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number"
                                                id="pagination[categories_per_page]"
                                                name="pagination[categories_per_page]"
                                                value="{{ $themeOptions['pagination']['categories_per_page'] ?? old('categories_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[categories_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="pagination[provider_list_per_page]">{{ __('static.theme_options.provider_list_per_page') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number"
                                                id="pagination[provider_list_per_page]"
                                                name="pagination[provider_list_per_page]"
                                                value="{{ $themeOptions['pagination']['provider_list_per_page'] ?? old('provider_list_per_page') }}"
                                                placeholder="{{ __('static.theme_options.enter_value') }}"
                                                min="1">
                                            @error('pagination[provider_list_per_page]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade tab2-card" id="about_us_option">
                                    <ul class="nav mb-3 nav-tabs" id="pills-tab">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="about_tab" data-bs-toggle="pill"
                                                href="#about">About</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="work_tab" data-bs-toggle="pill" href="#work">Work
                                                Banner</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="provider_tab" data-bs-toggle="pill"
                                                href="#provider">Providers List</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="user_tab" data-bs-toggle="pill"
                                                href="#user">Testimonial</a>
                                        </li>
                                        <!-- Add more nav items as needed -->
                                    </ul>

                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="about">
                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[status]">{{ __('Status') }}</label>
                                                <div class="col-md-10">
                                                    <div class="editor-space">
                                                        <label class="switch">
                                                            @if (isset($themeOptions['about_us']['status']))
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[status]" value="1"
                                                                    {{ $themeOptions['about_us']['status'] ? 'checked' : '' }}>
                                                            @else
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[status]" value="1">
                                                            @endif
                                                            <span class="switch-state"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[left_bg_image_url]"
                                                    class="col-md-2">{{ __('static.theme_options.left_bg_image') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="file"
                                                        id="about_us[left_bg_image_url]"
                                                        name="about_us[left_bg_image_url]">
                                                    @error('about_us[left_bg_image_url]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                    <span
                                                        class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                                </div>
                                            </div>
                                            @isset($themeOptions['about_us']['left_bg_image_url'])
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-2"></div>
                                                        <div class="col-md-10">
                                                            <div class="image-list">
                                                                <div class="image-list-detail">
                                                                    <div class="position-relative">
                                                                        <img src="{{ asset($themeOptions['about_us']['left_bg_image_url']) }}"
                                                                            id="{{ $themeOptions['about_us']['left_bg_image_url'] }}"
                                                                            alt="Float image" class="image-list-item">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endisset

                                            <div class="form-group row">
                                                <label for="about_us[right_bg_image_url]"
                                                    class="col-md-2">{{ __('static.theme_options.right_bg_image') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="file"
                                                        id="about_us[right_bg_image_url]"
                                                        name="about_us[right_bg_image_url]">
                                                    @error('about_us[right_bg_image_url]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                    <span
                                                        class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                                </div>
                                            </div>
                                            @isset($themeOptions['about_us']['right_bg_image_url'])
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-2"></div>
                                                        <div class="col-md-10">
                                                            <div class="image-list">
                                                                <div class="image-list-detail">
                                                                    <div class="position-relative">
                                                                        <img src="{{ asset($themeOptions['about_us']['right_bg_image_url']) }}"
                                                                            id="{{ $themeOptions['about_us']['right_bg_image_url'] }}"
                                                                            alt="Float image" class="image-list-item">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endisset

                                            <div class="form-group row">
                                                <label for="about_us[title]"
                                                    class="col-md-2">{{ __('static.theme_options.title') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text" id="about_us[title]"
                                                        name="about_us[title]"
                                                        value="{{ $themeOptions['about_us']['title'] ?? old('about_us[title]') }}"
                                                        placeholder="{{ __('static.theme_options.enter_title') }}">
                                                    @error('about_us[title]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[description]"
                                                    class="col-md-2">{{ __('static.theme_options.description') }}</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="about_us[description]" id="about_us[description]"
                                                        placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['about_us']['description'] ?? old('about_us[description]') }}</textarea>
                                                    @error('about_us[description]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[sub_title1]">{{ __('Sub Title 1') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text" id="about_us[sub_title1]"
                                                        name="about_us[sub_title1]"
                                                        value="{{ $themeOptions['about_us']['sub_title1'] ?? old('about_us[sub_title1]') }}"
                                                        placeholder="{{ __('static.theme_options.enter_title') }}">
                                                    @error('about_us[sub_title1]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[description1]"
                                                    class="col-md-2">{{ __('static.theme_options.description') }}</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="about_us[description1]" id="about_us[description1]"
                                                        placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['about_us']['description1'] ?? old('about_us[description1]') }}</textarea>
                                                    @error('about_us[description1]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[sub_title2]">{{ __('Sub Title 2') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text" id="about_us[sub_title2]"
                                                        name="about_us[sub_title2]"
                                                        value="{{ $themeOptions['about_us']['sub_title2'] ?? old('about_us[sub_title2]') }}"
                                                        placeholder="{{ __('static.theme_options.enter_title') }}">
                                                    @error('about_us[sub_title2]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[description2]"
                                                    class="col-md-2">{{ __('static.theme_options.description') }}</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="about_us[description2]" id="about_us[description2]"
                                                        placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['about_us']['description2'] ?? old('about_us[description2]') }}</textarea>
                                                    @error('about_us[description2]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[sub_title3]">{{ __('Sub Title 3') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text" id="about_us[sub_title3]"
                                                        name="about_us[sub_title3]"
                                                        value="{{ $themeOptions['about_us']['sub_title3'] ?? old('about_us[sub_title3]') }}"
                                                        placeholder="{{ __('static.theme_options.enter_title') }}">
                                                    @error('about_us[sub_title3]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[description3]"
                                                    class="col-md-2">{{ __('static.theme_options.description') }}</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="about_us[description3]" id="about_us[description3]"
                                                        placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['about_us']['description3'] ?? old('about_us[description3]') }}</textarea>
                                                    @error('about_us[description3]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[sub_title4]">{{ __('Sub Title 4') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text" id="about_us[sub_title4]"
                                                        name="about_us[sub_title4]"
                                                        value="{{ $themeOptions['about_us']['sub_title4'] ?? old('about_us[sub_title4]') }}"
                                                        placeholder="{{ __('static.theme_options.enter_title') }}">
                                                    @error('about_us[sub_title4]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[description4]"
                                                    class="col-md-2">{{ __('static.theme_options.description') }}</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="about_us[description4]" id="about_us[description4]"
                                                        placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['about_us']['description4'] ?? old('about_us[description4]') }}</textarea>
                                                    @error('about_us[description4]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[sub_title5]">{{ __('Sub Title 5') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text" id="about_us[sub_title5]"
                                                        name="about_us[sub_title5]"
                                                        value="{{ $themeOptions['about_us']['sub_title5'] ?? old('about_us[sub_title5]') }}"
                                                        placeholder="{{ __('static.theme_options.enter_title') }}">
                                                    @error('about_us[sub_title5]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="about_us[description5]"
                                                    class="col-md-2">{{ __('static.theme_options.description') }}</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="about_us[description5]" id="about_us[description5]"
                                                        placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $themeOptions['about_us']['description5'] ?? old('about_us[description5]') }}</textarea>
                                                    @error('about_us[description5]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="provider">
                                            <div class="form-group row">
                                                <label for="about_us[provider_title]"
                                                    class="col-md-2">{{ __('Title') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text"
                                                        id="about_us[provider_title]" name="about_us[provider_title]"
                                                        value="{{ $themeOptions['about_us']['provider_title'] ?? old('about_us[provider_title]') }}"
                                                        placeholder="{{ __('Enter Title') }}">
                                                    @error('about_us[provider_title]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[provider_ids]">{{ __('Providers') }}
                                                </label>
                                                <div class="col-md-10 error-div select-dropdown">
                                                    <select class="select-2 form-control" id="about_us[provider_ids][]"
                                                        search="true" name="about_us[provider_ids][]"
                                                        data-placeholder="{{ __('Select Providers') }}" multiple>
                                                        <option></option>
                                                        @foreach ($providers as $key => $value)
                                                            <option value="{{ $key }}"
                                                                {{ (is_array(old('provider_ids')) && in_array($key, old('provider_ids'))) || (isset($themeOptions['about_us']['provider_ids']) && in_array($key, $themeOptions['about_us']['provider_ids'])) ? 'selected' : '' }}>
                                                                {{ $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('provider_ids')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[provider_status]">{{ __('Status') }}</label>
                                                <div class="col-md-10">
                                                    <div class="editor-space">
                                                        <label class="switch">
                                                            @if (isset($themeOptions['about_us']['provider_status']))
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[provider_status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[provider_status]" value="1"
                                                                    {{ $themeOptions['about_us']['provider_status'] ? 'checked' : '' }}>
                                                            @else
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[provider_status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[provider_status]" value="1">
                                                            @endif
                                                            <span class="switch-state"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="user">                                            <div class="form-group row">
                                                <label for="about_us[testimonial_title]"
                                                    class="col-md-2">{{ __('Title') }}</label>
                                                <div class="col-md-10">
                                                    <input class="form-control" type="text"
                                                        id="about_us[testimonial_title]"
                                                        name="about_us[testimonial_title]"
                                                        value="{{ $themeOptions['about_us']['testimonial_title'] ?? old('about_us[testimonial_title]') }}"
                                                        placeholder="{{ __('Enter Title') }}">
                                                    @error('about_us[testimonial_title]')
                                                        <span class="invalid-feedback d-block">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[testimonial_status]">{{ __('Status') }}</label>
                                                <div class="col-md-10">
                                                    <div class="editor-space">
                                                        <label class="switch">
                                                            @if (isset($themeOptions['about_us']['testimonial_status']))
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[testimonial_status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[testimonial_status]" value="1"
                                                                    {{ $themeOptions['about_us']['testimonial_status'] ? 'checked' : '' }}>
                                                            @else
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[testimonial_status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[testimonial_status]" value="1">
                                                            @endif
                                                            <span class="switch-state"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="work">
                                            <div class="form-group row">
                                                <label class="col-md-2"
                                                    for="about_us[banner_status]">{{ __('Status') }}</label>
                                                <div class="col-md-10">
                                                    <div class="editor-space">
                                                        <label class="switch">
                                                            @if (isset($themeOptions['about_us']['banner_status']))
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[banner_status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[banner_status]" value="1"
                                                                    {{ $themeOptions['about_us']['banner_status'] ? 'checked' : '' }}>
                                                            @else
                                                                <input class="form-control" type="hidden"
                                                                    name="about_us[banner_status]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="about_us[banner_status]" value="1">
                                                            @endif
                                                            <span class="switch-state"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade theme-option-tab-box tab2-card" id="page">
                                    <ul class="nav mb-3 nav-tabs" id="pills-tab">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="terms_and_conditions_tab"
                                                data-bs-toggle="pill" href="#terms_and_conditions">Terms & Conditions</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="privacy_policy_tab" data-bs-toggle="pill"
                                                href="#privacy_policy">Privacy Policy</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="terms_and_conditions">
                                            <div class="accordion theme-accordion"
                                                id="terms_and_conditions_BannersAccordion">
                                                @foreach ($themeOptions['terms_and_conditions']['banners'] ?? [] as $index => $banner)
                                                    @include(
                                                        'backend.theme-options.terms_and_condition_banners',
                                                        [
                                                            'index' => $index,
                                                            'banner' => $banner,
                                                        ]
                                                    )
                                                @endforeach
                                            </div>

                                            <button type="button" id="add_terms_and_conditions_Banner"
                                                class="btn btn-primary mt-3">Add Banner</button>
                                            <template id="terms_and_conditions_bannerTemplate">
                                                @include(
                                                    'backend.theme-options.terms_and_condition_banners',
                                                    [
                                                        'index' => '__INDEX__',
                                                        'banner' => null,
                                                    ]
                                                )
                                            </template>

                                        </div>

                                        <div class="tab-pane fade" id="privacy_policy">
                                            <div class="accordion theme-accordion" id="privacy_policy_BannersAccordion">
                                                @foreach ($themeOptions['privacy_policy']['banners'] ?? [] as $index => $banner)
                                                    @include(
                                                        'backend.theme-options.privacy_policy_banners',
                                                        [
                                                            'index' => $index,
                                                            'banner' => $banner,
                                                        ]
                                                    )
                                                @endforeach
                                            </div>

                                            <button type="button" id="add_privacy_policy_Banner"
                                                class="btn btn-primary mt-3">Add Banner</button>
                                            <template id="privacy_policy_bannerTemplate">
                                                @include('backend.theme-options.privacy_policy_banners', [
                                                    'index' => '__INDEX__',
                                                    'banner' => null,
                                                ])
                                            </template>

                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="authentication">
                                    <div class="form-group row">
                                        <label for="authentication[header_logo]"
                                            class="col-md-2">{{ __('static.theme_options.header_logo') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="file"
                                                id="authentication[header_logo]" name="authentication[header_logo]">
                                            @error('authentication[header_logo]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <span
                                                class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                        </div>
                                    </div>
                                    @isset($themeOptions['authentication']['header_logo'])
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-10">
                                                    <div class="image-list">
                                                        <div class="image-list-detail">
                                                            <div class="position-relative">
                                                                <img src="{{ asset($themeOptions['authentication']['header_logo']) }}"
                                                                    id="{{ $themeOptions['authentication']['header_logo'] }}"
                                                                    alt="Float image" class="image-list-item">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endisset

                                    <div class="form-group row">
                                        <label for="authentication[auth_images]"
                                            class="col-md-2">{{ __('static.theme_options.auth_images') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="file"
                                                id="authentication[auth_images]" name="authentication[auth_images]">
                                            @error('authentication[auth_images]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <span
                                                class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                        </div>
                                    </div>
                                    @isset($themeOptions['authentication']['auth_images'])
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-10">
                                                    <div class="image-list">
                                                        <div class="image-list-detail">
                                                            <div class="position-relative">
                                                                <img src="{{ asset($themeOptions['authentication']['auth_images']) }}"
                                                                    id="{{ $themeOptions['authentication']['auth_images'] }}"
                                                                    alt="Float image" class="image-list-item">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endisset

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="authentication[title]">{{ __('Heading') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="authentication[title]"
                                                name="authentication[title]"
                                                value="{{ $themeOptions['authentication']['title'] ?? old('authentication[title]') }}"
                                                placeholder="{{ __('Enter App Store link') }}">
                                            @error('authentication[title]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="authentication[description]">{{ __('Description') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text"
                                                id="authentication[description]" name="authentication[description]"
                                                value="{{ $themeOptions['authentication']['description'] ?? old('authentication[description]') }}"
                                                placeholder="{{ __('Enter App Store link') }}">
                                            @error('authentication[description]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="seoSection">
                                    <div class="form-group row">
                                        <label for="image"
                                            class="col-md-2">{{ __('static.theme_options.og_image') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="file" id="seo[og_image]"
                                                name="seo[og_image]">
                                            @error('seo[og_image]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <span
                                                class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                        </div>
                                    </div>
                                    @isset($themeOptions['seo']['og_image'])
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-2"></div>
                                                <div class="col-md-10">
                                                    <div class="image-list">
                                                        <div class="image-list-detail">
                                                            <div class="position-relative">
                                                                <img src="{{ asset($themeOptions['seo']['og_image']) }}"
                                                                    id="{{ $themeOptions['seo']['og_image'] }}"
                                                                    alt="OG Image" class="image-list-item">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endisset

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="site_name">{{ __('static.theme_options.og_title') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="seo[og_title]"
                                                name="seo[og_title]"
                                                value="{{ $themeOptions['seo']['og_title'] ?? old('og_title') }}"
                                                placeholder="{{ __('static.theme_options.enter_og_title') }}">
                                            @error('seo[og_title]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="site_name">{{ __('static.theme_options.meta_title') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="seo[meta_title]"
                                                name="seo[meta_title]"
                                                value="{{ $themeOptions['seo']['meta_title'] ?? old('meta_title') }}"
                                                placeholder="{{ __('static.theme_options.enter_meta_title') }}">
                                            @error('seo[meta_title]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="meta_tags">{{ __('static.theme_options.meta_tags') }}</label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" id="seo[meta_tags]"
                                                name="seo[meta_tags]"
                                                value="{{ $themeOptions['seo']['meta_tags'] ?? old('meta_tags') }}"
                                                placeholder="{{ __('static.theme_options.enter_meta_tags') }}">
                                            @error('seo[meta_tags]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="og_description"
                                            class="col-md-2">{{ __('OG Descriptions') }}</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="seo[og_description]" id="seo[og_description]"
                                                placeholder="{{ __('static.theme_options.enter_description') }}" rows="4">{{ $themeOptions['seo']['og_description'] ?? old('seo[og_description]') }}</textarea>
                                            @error('seo[og_description]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="meta_description"
                                            class="col-md-2">{{ __('Meta Descriptions') }}</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="seo[meta_description]" id="seo[meta_description]"
                                                placeholder="{{ __('static.theme_options.enter_description') }}" rows="4">{{ $themeOptions['seo']['meta_description'] ?? old('seo[meta_description]') }}</textarea>
                                            @error('seo[meta_description]')
                                                <span class="invalid-feedback d-block">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <button type="submit"
                                    class="btn btn-primary spinner-btn">{{ __('static.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/js/password-hide-show.js') }}"></script>
    <script>
        $(document).ready(function() {
            "use strict";

            $("#themeOptionForm").validate({
                ignore: [],
                rules: {

                    "general[dark_logo]": {
                        required: isDarkLogo,
                        accept: "image/jpeg, image/png"
                    },
                    "general[favicon]": {
                        required: isFavicon,
                        accept: "image/jpeg, image/png"
                    },
                    "seo[og_image]": {
                        accept: "image/jpeg, image/png"
                    },
                    "email[mail_mailer]": "required",
                    "email[mail_host]": "required",
                    "email[mail_port]": "required",
                    "email[mail_encryption]": "required",
                    "email[mail_username]": "required",
                    "email[mail_password]": "required",
                    "email[mail_from_name]": "required",
                    "email[mail_from_address]": "required",
                }
            });


            function isFavicon() {
                @if (isset($themeOptions['general']['favicon']))
                    return false;
                @else
                    return true;
                @endif
            }

            function isDarkLogo() {
                @if (isset($themeOptions['general']['dark_logo']))
                    return false;
                @else
                    return true;
                @endif
            }

        });
    </script>
    <script>
        $(document).ready(function() {
            "use strict";

            let bannerIndex = {{ count($themeOptions['about_us']['banners'] ?? []) }};

            $('#addBanner').click(function() {
                const template = $('#bannerTemplate').html().replace(/__INDEX__/g, bannerIndex);
                $('#aboutBannersAccordion').append(template);
                bannerIndex++;
            });

            // Remove a banner
            $(document).on('click', '.remove-banner', function() {
                $(this).closest('.accordion-item').remove();
            });

            "use strict";

            // Check if there are existing banners and get their count
            let termsBannerIndex = {{ count($themeOptions['terms_and_conditions']['banners'] ?? []) }};
            let existingBanners = @json($themeOptions['terms_and_conditions']['banners'] ?? []);

            let privacyBannerIndex = {{ count($themeOptions['privacy_policy']['banners'] ?? []) }};
            let existingPrivacyBanners = @json($themeOptions['privacy_policy']['banners'] ?? []);

            // If the banners array is empty, set bannerIndex to 0
            if (existingBanners.length === 0) {
                termsBannerIndex = 0; // Start from index 0 if no banners exist
            } else {
                // Find the next available index if there are existing banners
                while (existingBanners[termsBannerIndex]) {
                    termsBannerIndex++;
                }
            }

            if (existingPrivacyBanners.length === 0) {
                privacyBannerIndex = 0; // Start from index 0 if no banners exist
            } else {
                // Find the next available index if there are existing banners
                while (existingPrivacyBanners[privacyBannerIndex]) {
                    privacyBannerIndex++;
                }
            }

            // Function to initialize TinyMCE for title and description
            function initTinyMCE(index) {
                tinymce.init({
                    selector: '#banner_description_' + index, // Initialize TinyMCE for description field
                    toolbar: [
                        'newdocument | print preview | searchreplace | undo redo  | alignleft aligncenter alignright alignjustify | code',
                        'formatselect fontselect fontsizeselect | bold italic underline strikethrough | forecolor backcolor',
                        'removeformat | hr pagebreak | charmap subscript superscript insertdatetime | bullist numlist | outdent indent blockquote | table'
                    ],
                    plugins: [
                        "advlist autolink lists link image charmap print preview anchor",
                        "searchreplace visualblocks code fullscreen",
                        "insertdatetime media table contextmenu paste imagetools"
                    ],
                    menubar: false,
                    image_title: true,
                    automatic_uploads: true,
                    file_picker_types: 'image',
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: false,
                    branding: false
                });
            }

            // Add a new banner dynamically
            $('#add_terms_and_conditions_Banner').click(function() {
                const template = $('#terms_and_conditions_bannerTemplate').html().replace(/__INDEX__/g,
                    termsBannerIndex); // Replace __INDEX__ with termsBannerIndex
                $('#terms_and_conditions_BannersAccordion').append(
                    template); // Append the new banner template

                // Initialize TinyMCE for the new description field
                initTinyMCE(termsBannerIndex);

                // Increment the banner index for the next banner
                termsBannerIndex++;
            });

            // Add a new banner dynamically
            $('#add_privacy_policy_Banner').click(function() {
                const template = $('#privacy_policy_bannerTemplate').html().replace(/__INDEX__/g,
                    privacyBannerIndex); // Replace __INDEX__ with termsBannerIndex
                $('#privacy_policy_BannersAccordion').append(template); // Append the new banner template

                // Initialize TinyMCE for the new description field
                initTinyMCE(privacyBannerIndex);

                // Increment the banner index for the next banner
                privacyBannerIndex++;
            });

            // Remove a banner
            $(document).on('click', '.remove-banner', function() {
                $(this).closest('.accordion-item').remove();
            });

            // Initialize TinyMCE for existing banners if they exist
            @foreach ($themeOptions['terms_and_conditions']['banners'] ?? [] as $index => $banner)
                initTinyMCE({{ $index }}); // Initialize TinyMCE for already existing banners
            @endforeach

            @foreach ($themeOptions['privacy_policy']['banners'] ?? [] as $index => $banner)
                initTinyMCE({{ $index }}); // Initialize TinyMCE for already existing banners
            @endforeach

            const colorInput = $('.primary-color');
            const colorPickerSpan = $('.color-picker');

            // Initialize span with the initial color input value
            colorPickerSpan.text(colorInput.val());

            // Update span text content when the color input value changes
            colorInput.on('input', function() {
                colorPickerSpan.text($(this).val());
            });
        });
    </script>
@endpush
