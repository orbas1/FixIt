@extends('backend.layouts.master')

@section('title', __('static.home_pages.home_page'))

@section('content')
    @use('App\Models\Settings')
    @use('App\Models\Service')
    @use('App\Models\Category')
    @use('App\Models\Blog')
    @use('App\Models\ServicePackage')
    @use('app\Helpers\Helpers')
    @php
        $services = Service::whereNull('deleted_at')->where('status', 1)?->pluck('title', 'id');
        $categories = Category::where('category_type', 'service')->whereNull('deleted_at')?->pluck('title', 'id');
        $providers = Helpers::getProviders()?->pluck('name', 'id');
        $blogs = Blog::whereNull('deleted_at')->where('status', 1)?->pluck('title', 'id');
        // $service_packages = ServicePackage::whereNull('deleted_at')?->pluck('title', 'id');
        
        $service_packages = ServicePackage::whereNull('deleted_at')->where('status', true)->whereDate('ended_at', '>=', now())->pluck('title', 'id');
        $bannerServices = Service::whereNull('deleted_at')?->where('status', true)?->get(['id', 'title']);
        $bannerCategories = Category::whereNull('deleted_at')?->where('category_type', 'service')?->where('status', true)?->get(['id', 'title']);
        $bannerServicePackages = ServicePackage::whereNull('deleted_at')?->where('status', true)?->get(['id', 'title']);
    @endphp
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.home_pages.home_page') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-sm-0 g-3">
                        <div class="col-xxl-3 col-xl-4 col-12">
                            <div class="vertical-tabs">
                                <div class="nav nav-pills" id="v-pills-tab">
                                    <a class="nav-link active" id="v-pills-tabContent" data-bs-toggle="pill" href="#home_banner">
                                        <i class="ri-home-line"></i>{{ __('static.home_pages.home_banner') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#categories_icon_list">
                                        <i class="ri-menu-search-line"></i>{{ __('static.home_pages.categories_icon_list') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" href="#value_banners">
                                        <i class="ri-image-line"></i>{{ __('static.home_pages.value_banners') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#service_list_1">
                                        <i class="ri-server-line"></i>{{ __('static.home_pages.service_list') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#download">
                                        <i class="ri-download-2-line"></i>{{ __('static.home_pages.download') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#providers_list">
                                        <i class="ri-user-line"></i>{{ __('static.home_pages.providers_list') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#special_offers_section">
                                        <i class="ri-user-line"></i>{{ __('static.home_pages.special_offers') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#service_packages_list">
                                        <i class="ri-server-line"></i>{{ __('static.home_pages.service_packages_list') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#blogs_list">
                                        <i class="ri-blogger-line"></i>{{ __('static.home_pages.blogs_list') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#custom_job">
                                        <i class="ri-search-eye-line"></i>{{ __('static.home_pages.custom_job') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#become_a_provider">
                                        <i class="ri-user-line"></i>{{ __('static.home_pages.become_a_provider') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#testimonial">
                                        <i class="ri-terminal-box-line"></i>{{ __('static.home_pages.testimonial') }}
                                    </a>
                                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#news_letter">
                                        <i class="ri-news-line"></i>{{ __('static.home_pages.news_letter') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="vertical-tabs">
                                <form method="POST" class="needs-validation user-add h-100" id="homePageForm" action="{{ route('backend.update.home_page', $homePageId) }}" enctype="multipart/form-data">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="locale" value="{{ Session::get('locale') }}">
                                    @isset($homePage)
                                        <div class="form-group row">
                                            <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
                                            <div class="col-md-10">
                                                <ul class="language-list">
                                                    @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                                                        <li>
                                                            <a href="{{ route('backend.home_page.index', ['locale' => $lang->locale]) }}" class="language-switcher {{ Session::get('locale') === $lang->locale ? 'active' : '' }}" target="_blank">
                                                                <img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i data-feather="arrow-up-right"></i>
                                                            </a>
                                                        </li>
                                                    @empty
                                                        <li>
                                                            <a href="{{ route('backend.home_page.index', ['locale' => Session::get('locale', 'en')]) }}" class="language-switcher active" target="blank"><img src="{{ asset('admin/images/flags/LR.png') }}" alt="">English
                                                                <i data-feather="arrow-up-right"></i>
                                                            </a>
                                                        </li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    @endisset
                                    <div class="tab-content w-100" id="v-pills-tabContent">
                                        <div class="tab-pane fade show active" id="home_banner">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="home_banner[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="home_banner[title]" name="home_banner[title]" value="{{ $homePage['home_banner']['title'] ?? old('home_banner[title]') }}" placeholder="{{ __('static.home_pages.enter_home_banner_title') }}">
                                                        @error('home_banner[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.home_banner_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="home_banner[animate_text]">{{ __('static.home_pages.animate_text') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" maxlength="20" id="home_banner[animate_text]" name="home_banner[animate_text]" value="{{ $homePage['home_banner']['animate_text'] ?? old('home_banner[animate_text]') }}" placeholder="{{ __('static.home_pages.enter_home_banner_animated_text') }}">
                                                        @error('home_banner[animate_text]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.animated_text_help') }}</span>

                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="address" class="col-md-2">{{ __('static.home_pages.description') }}</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" rows="4" name="home_banner[description]" id="home_banner[description]" placeholder="{{ __('static.home_pages.enter_description') }}" cols="50">{{ $homePage['home_banner']['description'] ?? old('home_banner[description]') }} </textarea>
                                                        @error('home_banner[description]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.home_banner_description_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="home_banner[search_enable]">{{ __('static.home_pages.search_box') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['home_banner']['search_enable']))
                                                                    <input class="form-control" type="hidden" name="home_banner[search_enable]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="home_banner[search_enable]" value="1" {{ $homePage['home_banner']['search_enable'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden" name="home_banner[search_enable]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="home_banner[search_enable]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.search_box_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="service_ids">{{ __('static.home_pages.services') }}</label>
                                                    <div class="col-md-10 error-div select-dropdown">
                                                        <select class="select-2 form-control" id="home_banner[service_ids][]" search="true" name="home_banner[service_ids][]" data-placeholder="{{ __('static.home_pages.select_services') }}" multiple>
                                                            <option></option>
                                                            @foreach ($services as $key => $value)
                                                                <option value="{{ $key }}" {{ (is_array(old('service_ids')) && in_array($key, old('service_ids'))) || (isset($homePage['home_banner']['service_ids']) && in_array($key, $homePage['home_banner']['service_ids'])) ? 'selected' : '' }}>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('service_ids')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.services_selection_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="home_banner[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['home_banner']['status']))
                                                                    <input class="form-control" type="hidden" name="home_banner[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="home_banner[status]" value="1" {{ $homePage['home_banner']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden" name="home_banner[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="home_banner[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.status_toggle_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="categories_icon_list">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2" for="categories_icon_list[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="categories_icon_list[title]" name="categories_icon_list[title]" value="{{ $homePage['categories_icon_list']['title'] ?? old('categories_icon_list[title]') }}" placeholder="{{ __('static.home_pages.enter_category_title') }}">
                                                        @error('categories_icon_list[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.category_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="category_ids">{{ __('static.home_pages.categories') }}</label>
                                                    <div class="col-md-10 error-div select-dropdown">
                                                        <select class="select-2 form-control" id="categories_icon_list[category_ids][]" search="true" name="categories_icon_list[category_ids][]" data-placeholder="{{ __('static.home_pages.select_categories') }}" multiple>
                                                            <option></option>
                                                            @foreach ($categories as $key => $value)
                                                                <option value="{{ $key }}" {{ (is_array(old('category_ids')) && in_array($key, old('category_ids'))) || (isset($homePage['categories_icon_list']['category_ids']) && in_array($key, $homePage['categories_icon_list']['category_ids'])) ? 'selected' : '' }}>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('category_ids')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.select_categories_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="categories_icon_list[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['categories_icon_list']['status']))
                                                                    <input class="form-control" type="hidden" name="categories_icon_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="categories_icon_list[status]" value="1" {{ $homePage['categories_icon_list']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden" name="categories_icon_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="categories_icon_list[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.category_status_toggle_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="value_banners">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2" for="value_banners[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="value_banners[title]" name="value_banners[title]" value="{{ $homePage['value_banners']['title'] ?? old('value_banners[title]') }}" placeholder="{{ __('static.home_pages.enter_value_banner_title') }}">
                                                        @error('value_banners[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="value_banners[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['value_banners']['status']))
                                                                    <input class="form-control" type="hidden" name="value_banners[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="value_banners[status]" value="1" {{ $homePage['value_banners']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden" name="value_banners[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="value_banners[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion theme-accordion" id="valueBannersAccordion">
                                                @foreach ($homePage['value_banners']['banners'] ?? [] as $index => $banner)
                                                    @include('backend.home-page.banners', [
                                                        'index' => $index,
                                                        'banner' => $banner,
                                                    ])
                                                @endforeach
                                            </div>

                                            <button type="button" id="addBanner" class="btn btn-primary mt-3">Add Banner</button>
                                            <template id="bannerTemplate">
                                                @include('backend.home-page.banners', [
                                                    'index' => '__INDEX__',
                                                    'banner' => null,
                                                ])
                                            </template>
                                        </div>

                                        <div class="tab-pane fade" id="service_list_1">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2" for="service_list_1[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="service_list_1[title]" name="service_list_1[title]" value="{{ $homePage['service_list_1']['title'] ?? old('service_list_1[title]') }}" placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('service_list_1[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.service_list_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="service_ids">{{ __('static.home_pages.services') }} </label>
                                                    <div class="col-md-10 error-div select-dropdown">
                                                        <select class="select-2 form-control" id="service_list_1[service_ids][]" search="true" name="service_list_1[service_ids][]" data-placeholder="{{ __('static.home_pages.select_services') }}" multiple>
                                                            <option></option>
                                                            @foreach ($services as $key => $value)
                                                                <option value="{{ $key }}" {{ (is_array(old('service_ids')) && in_array($key, old('service_ids'))) || (isset($homePage['service_list_1']['service_ids']) && in_array($key, $homePage['service_list_1']['service_ids'])) ? 'selected' : '' }}>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('service_ids')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.select_services_help') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-md-2" for="service_list_1[status]">{{ __('static.home_pages.status') }} </label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['service_list_1']['status']))
                                                                    <input class="form-control" type="hidden" name="service_list_1[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="service_list_1[status]" value="1"
                                                                        {{ $homePage['service_list_1']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="service_list_1[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="service_list_1[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.service_status_toggle_help') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="download">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="download[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['download']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="download[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="download[status]" value="1"
                                                                        {{ $homePage['download']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="download[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="download[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.home_pages.download_status_help') }}</span>

                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="image" class="col-md-2">{{ __('static.home_pages.image_gif') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="file" id="download[image_url]"
                                                            name="download[image_url]">
                                                        @error('download[image_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.upload_image_gif_help') }}</span>

                                                    </div>
                                                </div>
                                                @isset($homePage['download']['image_url'])
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    <div class="image-list-detail">
                                                                        <div class="position-relative">
                                                                            <img src="{{ asset($homePage['download']['image_url']) }}"
                                                                                id="{{ $homePage['download']['image_url'] }}"
                                                                                alt="Header Logo" class="image-list-item">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endisset

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="download[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="download[title]"
                                                            name="download[title]"
                                                            value="{{ $homePage['download']['title'] ?? old('download[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('download[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.download_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="address" class="col-md-2">{{ __('static.home_pages.description') }}<span>
                                                        </span></label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" rows="4" name="download[description]" id="download[description]"
                                                            placeholder="{{ __('static.home_pages.enter_description') }}" cols="50">{{ $homePage['download']['description'] ?? old('download[description]') }} </textarea>
                                                        @error('download[description]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.download_description_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="providers_list">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="providers_list[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="providers_list[title]"
                                                            name="providers_list[title]"
                                                            value="{{ $homePage['providers_list']['title'] ?? old('providers_list[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('providers_list[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.providers_list_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="provider_ids">{{ __('static.home_pages.providers') }} </label>
                                                    <div class="col-md-10 error-div select-dropdown">
                                                        <select class="select-2 form-control"
                                                            id="providers_list[provider_ids][]" search="true"
                                                            name="providers_list[provider_ids][]"
                                                            data-placeholder="{{ __('static.home_pages.select_providers') }}" multiple>
                                                            <option></option>
                                                            @foreach ($providers as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ (is_array(old('provider_ids')) && in_array($key, old('provider_ids'))) || (isset($homePage['providers_list']['provider_ids']) && in_array($key, $homePage['providers_list']['provider_ids'])) ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('provider_ids')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.select_providers_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="providers_list[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['providers_list']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="providers_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="providers_list[status]" value="1"
                                                                        {{ $homePage['providers_list']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="providers_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="providers_list[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.providers_list_status_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="special_offers_section">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2" for="special_offers_section[banner_section_title]">{{ __('static.home_pages.banner_section_title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="special_offers_section[banner_section_title]" name="special_offers_section[banner_section_title]" value="{{ $homePage['special_offers_section']['banner_section_title'] ?? old('special_offers_section[banner_section_title]') }}" placeholder="{{ __('static.home_pages.banner_section_title_placeholder') }}">
                                                        @error('special_offers_section[banner_section_title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.special_offers_banner_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="special_offers_section[service_section_title]">{{ __('static.home_pages.service_section_title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="special_offers_section[service_section_title]" name="special_offers_section[service_section_title]" value="{{ $homePage['special_offers_section']['service_section_title'] ?? old('special_offers_section[service_section_title]') }}" placeholder="{{ __('static.home_pages.service_section_title_placeholder') }}">
                                                        @error('special_offers_section[service_section_title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.special_offers_service_title_help') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="service_packages_list">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="service_packages_list[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="service_packages_list[title]"
                                                            name="service_packages_list[title]"
                                                            value="{{ $homePage['service_packages_list']['title'] ?? old('service_packages_list[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('service_packages_list[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.service_packages_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="service_packages_ids">{{ __('static.home_pages.service_packages') }} </label>
                                                    <div class="col-md-10 error-div select-dropdown">
                                                        <select class="select-2 form-control"
                                                            id="service_packages_list[service_packages_ids][]" search="true"
                                                            name="service_packages_list[service_packages_ids][]"
                                                            data-placeholder="{{ __('static.home_pages.select_service_packages') }}" multiple>
                                                            <option></option>
                                                            @foreach ($service_packages as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ (is_array(old('provider_ids')) && in_array($key, old('provider_ids'))) || (isset($homePage['service_packages_list']['service_packages_ids']) && in_array($key, $homePage['service_packages_list']['service_packages_ids'])) ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('service_packages_ids')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.service_packages_dropdown_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="service_packages_list[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['service_packages_list']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="service_packages_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="service_packages_list[status]" value="1"
                                                                        {{ $homePage['service_packages_list']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="service_packages_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="service_packages_list[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.service_packages_status_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="blogs_list">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="blogs_list[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="blogs_list[title]"
                                                            name="blogs_list[title]"
                                                            value="{{ $homePage['blogs_list']['title'] ?? old('blogs_list[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('blogs_list[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.blogs_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="address" class="col-md-2">{{ __('static.home_pages.description') }}<span>
                                                        </span></label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" rows="4" name="blogs_list[description]" id="blogs_list[description]" placeholder="{{ __('static.home_pages.enter_description') }}" cols="50">{{ $homePage['blogs_list']['description'] ?? old('blogs_list[description]') }}</textarea>
                                                        @error('blogs_list[description]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.blogs_description_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="blog_ids">{{ __('static.home_pages.blogs') }}</label>
                                                    <div class="col-md-10 error-div select-dropdown">
                                                        <select class="select-2 form-control" id="blogs_list[blog_ids][]"
                                                            search="true" name="blogs_list[blog_ids][]"
                                                            data-placeholder="{{ __('static.home_pages.select_blogs') }}" multiple>
                                                            <option></option>
                                                            @foreach ($blogs as $key => $value)
                                                                <option value="{{ $key }}"
                                                                    {{ (is_array(old('blog_ids')) && in_array($key, old('blog_ids'))) || (isset($homePage['blogs_list']['blog_ids']) && in_array($key, $homePage['blogs_list']['blog_ids'])) ? 'selected' : '' }}>
                                                                    {{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('blog_ids')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.blogs_dropdown_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="blogs_list[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['blogs_list']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="blogs_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="blogs_list[status]" value="1"
                                                                        {{ $homePage['blogs_list']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="blogs_list[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="blogs_list[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.blogs_status_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="custom_job">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2" for="custom_job[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['custom_job']['status']))
                                                                    <input class="form-control" type="hidden" name="custom_job[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="custom_job[status]" value="1" {{ $homePage['custom_job']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden" name="custom_job[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="custom_job[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.custom_job_status_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="image" class="col-md-2">{{ __('static.home_pages.image') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="file" id="custom_job[image_url]" name="custom_job[image_url]">
                                                        @error('custom_job[image_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.upload_image') }}</span>
                                                    </div>
                                                </div>
                                                @isset($homePage['custom_job']['image_url'])
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    <div class="image-list-detail">
                                                                        <div class="position-relative">
                                                                            <img src="{{ asset($homePage['custom_job']['image_url']) }}" id="{{ $homePage['custom_job']['image_url'] }}" alt="Header Logo" class="image-list-item">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endisset

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="custom_job[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="custom_job[title]" name="custom_job[title]" value="{{ $homePage['custom_job']['title'] ?? old('custom_job[title]') }}" placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('custom_job[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.custom_job_title_help') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2" for="custom_job[button_text]">{{ __('static.home_pages.button_text') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="custom_job[button_text]" name="custom_job[button_text]" value="{{ $homePage['custom_job']['button_text'] ?? old('custom_job[button_text]') }}" placeholder="{{ __('static.home_pages.enter_button_text') }}">
                                                        @error('custom_job[button_text]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.custom_job_button_text_help') }}</span>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="become_a_provider">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="become_a_provider[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['become_a_provider']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="become_a_provider[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="become_a_provider[status]" value="1"
                                                                        {{ $homePage['become_a_provider']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="become_a_provider[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="become_a_provider[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="image" class="col-md-2">{{ __('static.home_pages.image') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="file"
                                                            id="become_a_provider[image_url]"
                                                            name="become_a_provider[image_url]">
                                                        @error('become_a_provider[image_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span
                                                            class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                                    </div>
                                                </div>
                                                @isset($homePage['become_a_provider']['image_url'])
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    <div class="image-list-detail">
                                                                        <div class="position-relative">
                                                                            <img src="{{ asset($homePage['become_a_provider']['image_url']) }}"
                                                                                id="{{ $homePage['become_a_provider']['image_url'] }}"
                                                                                alt="Float image" class="image-list-item">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endisset

                                                <div class="form-group row">
                                                    <label for="image" class="col-md-2">{{ __('static.home_pages.float_image_1') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="file"
                                                            id="become_a_provider[float_image_1_url]"
                                                            name="become_a_provider[float_image_1_url]">
                                                        @error('become_a_provider[float_image_1_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span
                                                            class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                                    </div>
                                                </div>
                                                @isset($homePage['become_a_provider']['float_image_1_url'])
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    <div class="image-list-detail">
                                                                        <div class="position-relative">
                                                                            <img src="{{ asset($homePage['become_a_provider']['float_image_1_url']) }}"
                                                                                id="{{ $homePage['become_a_provider']['float_image_1_url'] }}"
                                                                                alt="Become a image" class="image-list-item">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endisset

                                                <div class="form-group row">
                                                    <label for="image" class="col-md-2">{{ __('static.home_pages.float_image_2') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="file"
                                                            id="become_a_provider[float_image_2_url]"
                                                            name="become_a_provider[float_image_2_url]">
                                                        @error('become_a_provider[float_image_2_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span
                                                            class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                                    </div>
                                                </div>
                                                @isset($homePage['become_a_provider']['float_image_2_url'])
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    <div class="image-list-detail">
                                                                        <div class="position-relative">
                                                                            <img src="{{ asset($homePage['become_a_provider']['float_image_2_url']) }}"
                                                                                id="{{ $homePage['become_a_provider']['float_image_2_url'] }}"
                                                                                alt="Become a image" class="image-list-item">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endisset
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="become_a_provider[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="become_a_provider[title]" name="become_a_provider[title]"
                                                            value="{{ $homePage['become_a_provider']['title'] ?? old('become_a_provider[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('become_a_provider[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.become_provider_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="address" class="col-md-2">{{ __('static.home_pages.description') }}<span>
                                                        </span></label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" rows="4" name="become_a_provider[description]"
                                                            id="become_a_provider[description]" placeholder="{{ __('static.home_pages.enter_description') }}" cols="50">{{ $homePage['become_a_provider']['description'] ?? old('become_a_provider[description]') }} </textarea>
                                                        @error('become_a_provider[description]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.become_provider_description_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="become_a_provider[button_text]">{{ __('static.home_pages.button_text') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="become_a_provider[button_text]"
                                                            name="become_a_provider[button_text]"
                                                            value="{{ $homePage['become_a_provider']['button_text'] ?? old('become_a_provider[button_text]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_button_text') }}">
                                                        @error('become_a_provider[button_text]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.become_provider_button_text_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="become_a_provider[button_url]">{{ __('static.home_pages.button_link') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="become_a_provider[button_url]"
                                                            name="become_a_provider[button_url]"
                                                            value="{{ $homePage['become_a_provider']['button_url'] ?? old('become_a_provider[button_url]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_button_text') }}">
                                                        @error('become_a_provider[button_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.become_provider_button_url_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="testimonial">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="testimonial[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="testimonial[title]"
                                                            name="testimonial[title]"
                                                            value="{{ $homePage['testimonial']['title'] ?? old('testimonial[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('testimonial[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.testimonial_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="testimonial[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['testimonial']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="testimonial[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="testimonial[status]" value="1"
                                                                        {{ $homePage['testimonial']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="testimonial[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="testimonial[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                        <span class="help-text">{{ __('static.theme_options.testimonial_status_help') }}</span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="news_letter">
                                            <div>
                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="news_letter[title]">{{ __('static.home_pages.title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text" id="news_letter[title]"
                                                            name="news_letter[title]"
                                                            value="{{ $homePage['news_letter']['title'] ?? old('news_letter[title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_title') }}">
                                                        @error('news_letter[title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.newsletter_title_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="news_letter[sub_title]">{{ __('static.home_pages.sub_title') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="news_letter[sub_title]" name="news_letter[sub_title]"
                                                            value="{{ $homePage['news_letter']['sub_title'] ?? old('news_letter[sub_title]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_sub_title') }}">
                                                        @error('news_letter[sub_title]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.newsletter_subtitle_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2"
                                                        for="news_letter[button_text]">{{ __('static.home_pages.button_text') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="news_letter[button_text]" name="news_letter[button_text]"
                                                            value="{{ $homePage['news_letter']['button_text'] ?? old('news_letter[button_text]') }}"
                                                            placeholder="{{ __('static.home_pages.enter_button_text') }}">
                                                        @error('news_letter[button_text]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span class="help-text">{{ __('static.theme_options.newsletter_button_text_help') }}</span>

                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="image" class="col-md-2">{{ __('static.home_pages.image') }}</label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="file"
                                                            id="news_letter[bg_image_url]" name="news_letter[bg_image_url]">
                                                        @error('news_letter[bg_image_url]')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                        <span
                                                            class="help-text">{{ __('static.theme_options.upload_logo_image_size') }}</span>
                                                    </div>
                                                </div>
                                                @isset($homePage['news_letter']['bg_image_url'])
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    <div class="image-list-detail">
                                                                        <div class="position-relative">
                                                                            <img src="{{ asset($homePage['news_letter']['bg_image_url']) }}"
                                                                                id="{{ $homePage['news_letter']['bg_image_url'] }}"
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
                                                        for="news_letter[status]">{{ __('static.home_pages.status') }}</label>
                                                    <div class="col-md-10">
                                                        <div class="editor-space">
                                                            <label class="switch">
                                                                @if (isset($homePage['news_letter']['status']))
                                                                    <input class="form-control" type="hidden"
                                                                        name="news_letter[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="news_letter[status]" value="1"
                                                                        {{ $homePage['news_letter']['status'] ? 'checked' : '' }}>
                                                                @else
                                                                    <input class="form-control" type="hidden"
                                                                        name="news_letter[status]" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="news_letter[status]" value="1">
                                                                @endif
                                                                <span class="switch-state"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="button-box">
                                            <button type="submit"
                                                class="btn btn-primary spinner-btn">{{ __('static.save') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
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

            let bannerIndex = {{ count($homePage['value_banners']['banners'] ?? []) }};

            // Custom image MIME validator
            $.validator.addMethod("imageMime", function(value, element) {
                const file = element.files && element.files[0];
                if (file) {
                    return ["image/png", "image/jpeg", "image/jpg"].includes(file.type);
                }
                return true;
            }, "Please upload a valid image file (JPG, PNG, JPEG).");

            const formValidator = $('#homePageForm').validate({
                ignore: [],
                rules: {}
            });

            function updateBannerFields(index) {
                let redirectType = $('#redirectType-' + index).val();
                let dynamicSelect = $('#dynamicSelect-' + index);
                let externalUrlField = $('#externalUrl-' + index);
                let dynamicSelectInput = $('#dynamicSelectInput-' + index);
                let buttonUrlInputName = `value_banners[banners][${index}][button_url]`;
                let buttonUrlInput = $(`[name="${buttonUrlInputName}"]`);

                dynamicSelect.hide();
                externalUrlField.hide();
                dynamicSelectInput.empty();

                // Remove old rule
                formValidator.settings.rules[buttonUrlInputName] = {};

                if (redirectType === 'service') {
                    dynamicSelect.show();
                    @foreach ($bannerServices as $service)
                        dynamicSelectInput.append('<option value="{{ $service->id }}" {{ isset($banner['redirect_id']) && $banner['redirect_id'] == $service->id ? 'selected ' : '' }}>{{ $service->title }}</option>');
                    @endforeach
                } else if (redirectType === 'package') {
                    dynamicSelect.show();
                    @foreach ($bannerServicePackages as $servicePackage)
                        dynamicSelectInput.append('<option value="{{ $servicePackage->id }}" {{ isset($banner['redirect_id']) && $banner['redirect_id'] == $servicePackage->id ? 'selected ' : '' }}>{{ $servicePackage->title }}</option>');
                    @endforeach
                } else if (redirectType === 'external_url') {
                    externalUrlField.show();
                }

                dynamicSelectInput.select2();
            }

            @foreach ($homePage['value_banners']['banners'] ?? [] as $index => $banner)
                updateBannerFields({{ $index }});
            @endforeach
            @foreach ($homePage['value_banners']['banners'] ?? [] as $index => $banner)
                ['title', 'sale_tag', 'button_text', 'redirect_type'].forEach(field => {
                    const fieldName = `value_banners[banners][{{ $index }}][${field}]`;
                    formValidator.settings.rules[fieldName] = {
                        required: true,
                        ...(field === 'image_url' ? { imageMime: true } : {})
                    };
                });

                @if (($banner['redirect_type'] ?? '') === 'external_url')
                    formValidator.settings.rules[`value_banners[banners][{{ $index }}][button_url]`] = {
                        required: true,
                        url: true
                    };
                @endif
            @endforeach


            $(document).on('change', '.redirect-type', function() {
                const index = $(this).attr('id').split('-')[1];
                updateBannerFields(index);
            });

            $('#addBanner').click(function() {
                const lastBanner = $('.accordion-item').last();
                let isValid = true;

                lastBanner.find(':input').each(function() {
                    if (!$(this).valid()) {
                        isValid = false;
                        const collapse = $(this).closest('.accordion-collapse');
                        if (!collapse.hasClass('show')) {
                            collapse.collapse('show');
                        }
                    }
                });

                if (!isValid) return;

                const template = $('#bannerTemplate').html().replace(/__INDEX__/g, bannerIndex);
                $('#valueBannersAccordion').append(template);

                // Add validation rules
                [
                    'title',
                    'sale_tag',
                    'button_text',
                    'redirect_type',
                    'image_url'
                ].forEach(field => {
                    const fieldName = `value_banners[banners][${bannerIndex}][${field}]`;
                    formValidator.settings.rules[fieldName] = {
                        required: true,
                        ...(field === 'image_url' ? { imageMime: true } : {})
                    };
                });

                $(document).on('change', '.redirect-type', function() {
                    const index = $(this).attr('id').split('-')[1];
                    updateBannerFields(index);
                });

                bannerIndex++;
            });

            $(document).on('click', '.remove-banner', function() {
                $(this).closest('.accordion-item').remove();
            });
        });
    </script>
@endpush
