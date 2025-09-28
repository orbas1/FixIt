<div class="row g-sm-4 g-3">
    <div class="col-xl-8">
        <div class="row g-sm-4 g-3">
            <div class="col-12">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>{{ isset($blog) ? __('static.blog.edit') : __('static.blog.create') }}
                            ({{ request('locale') ?? app()->getLocale() }})
                        </h5>
                    </div>
                    <div class="card-body">

                            <div class="form-group row">
                                <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
                                <div class="col-md-10">
                                    <ul class="language-list">
                                        @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                                            @if(isset($blog))
                                                <li>
                                                    <a href="{{ route('backend.blog.edit', ['blog' => $blog->id, 'locale' => $lang->locale]) }}"
                                                        class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                        target="_blank"><img
                                                            src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                            alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                            data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="{{ route('backend.blog.create', ['locale' => $lang->locale]) }}"
                                                        class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                        target="_blank"><img
                                                            src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                            alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                            data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @endif
                                        @empty
                                            @if(isset($blog))
                                                <li>
                                                    <a href="{{ route('backend.blog.edit', ['blog' => $blog->id, 'locale' => Session::get('locale', 'en')]) }}"
                                                        class="language-switcher active" target="blank"><img
                                                            src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i
                                                            data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="{{ route('backend.blog.create', ['locale' => $lang->locale]) }}"
                                                        class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                        target="_blank"><img
                                                            src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                            alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                            data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @endif
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                        <input type="hidden" name="locale" value="{{ request('locale') }}">
                        <div class="form-group row">
                            <label class="col-md-2" for="title">{{ __('static.title') }}
                                ({{ request('locale', app()->getLocale()) }})<span>
                                    *</span></label>
                            <div class="col-md-10 input-copy-box">
                                <input class='form-control' type="text" name="title" id="title"
                                    value="{{ isset($blog->title) ? $blog->getTranslation('title', request('locale', app()->getLocale())) : old('title') }}"
                                    placeholder="{{ __('static.blog.enter_title') }} ({{ request('locale', app()->getLocale()) }})">
                                @error('title')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <!-- Copy Icon -->
                                <span class="input-copy-icon" data-tooltip="Copy">
                                    <i data-feather="copy"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="address" class="col-md-2">{{ __('static.blog.description') }}
                                ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                            <div class="col-md-10 input-copy-box">
                                <textarea class="form-control" rows="4" name="description" id="description"
                                    placeholder="{{ __('static.blog.enter_description') }} ({{ request('locale', app()->getLocale()) }})"
                                    cols="50">{{ isset($blog->description) ? $blog->getTranslation('description', request('locale', app()->getLocale())) : old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <!-- Copy Icon -->
                                <span class="input-copy-icon" data-tooltip="Copy">
                                    <i data-feather="copy"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="address" class="col-md-2">{{ __('static.content') }}
                                ({{ request('locale', app()->getLocale()) }})<span>
                                    *</span></label>
                            <div class="col-md-10 d-flex flex-column-reverse">
                                <textarea class="form-control summary-ckeditor" id="content" rows="4" name="content"
                                    cols="50">{{ isset($blog->content) ? $blog->getTranslation('content', request('locale', app()->getLocale())) : old('content') }}</textarea>
                                @error('content')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="image[]" class="col-md-2">{{ __('static.blog.image') }}
                                ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                            <div class="col-md-10">
                                <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="image[]"
                                    name="image[]" multiple>
                                @error('image')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        @if (isset($blog))
                                                @php
                                                    $locale = request('locale');
                                                    $mediaItems = $blog->getMedia('image')->filter(function ($media) use ($locale) {
                                                        return $media->getCustomProperty('language') === $locale;
                                                    });
                                                @endphp
                                                @if ($mediaItems->count() > 0)
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    @foreach ($mediaItems as $media)
                                                                        <div class="image-list-detail">
                                                                            <div class="position-relative">
                                                                                <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Blog Image"
                                                                                    class="image-list-item">
                                                                                <div class="close-icon">
                                                                                    <i data-feather="x"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                        @endif
                        <div class="form-group row">
                            <label for="web_image[]" class="col-md-2">{{ __('static.blog.web_image') }}
                                ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                            <div class="col-md-10">
                                <input class="form-control" type="file" id="web_image[]" name="web_image[]" multiple>
                                @error('web_image')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        @if (isset($blog))
                                                @php
                                                    $locale = request('locale');
                                                    $mediaItems = $blog
                                                        ->getMedia('web_image')
                                                        ->filter(function ($media) use ($locale) {
                                                            return $media->getCustomProperty('language') === $locale;
                                                        });
                                                @endphp
                                                @if ($mediaItems->count() > 0)
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-2"></div>
                                                            <div class="col-md-10">
                                                                <div class="image-list">
                                                                    @foreach ($mediaItems as $media)
                                                                        <div class="image-list-detail">
                                                                            <div class="position-relative">
                                                                                <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="Blog Image"
                                                                                    class="image-list-item">
                                                                                <div class="close-icon">
                                                                                    <i data-feather="x"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>Search Engine Optimization (SEO)</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('backend.blog.store') }}" id="blogForm" method="POST"
                            enctype="multipart/form-data">
                            <div class="form-group row">
                                <label class="col-md-2" for="meta_title">{{ __('static.blog.meta_title') }}
                                    ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                                <div class="col-md-10 input-copy-box">
                                    <input class='form-control' type="text" name="meta_title" id="meta_title"
                                        value="{{ isset($blog->meta_title) ? $blog->getTranslation('meta_title', request('locale', app()->getLocale())) : old('meta_title') }}"
                                        placeholder="{{ __('static.blog.enter_meta_title') }} ({{ request('locale', app()->getLocale()) }})">
                                    @error('meta_title')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <!-- Copy Icon -->
                                    <span class="input-copy-icon" data-tooltip="Copy">
                                        <i data-feather="copy"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="address" class="col-md-2">{{ __('static.blog.meta_description') }}
                                    ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                                <div class="col-md-10 input-copy-box">
                                    <textarea class="form-control" rows="4"
                                        placeholder="{{ __('static.blog.enter_meta_description') }} ({{ request('locale', app()->getLocale()) }})"
                                        id="meta_description" name="meta_description"
                                        cols="50">{{ isset($blog->meta_description) ? $blog->getTranslation('meta_description', request('locale', app()->getLocale())) : old('meta_description') }}</textarea>
                                    @error('meta_description')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <!-- Copy Icon -->
                                    <span class="input-copy-icon" data-tooltip="Copy">
                                        <i data-feather="copy"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="image" class="col-md-2">{{ __('static.page.meta_image') }}
                                    ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                                <div class="col-md-10">
                                    <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="meta_image"
                                        name="meta_image" multiple>
                                    @error('meta_image')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            @if (isset($blog))
                            @php
                                $locale = request('locale');
                                $mediaItems = $blog
                                    ->getMedia('meta_image')
                                    ->filter(function ($media) use ($locale) {
                                        return $media->getCustomProperty('language') === $locale;
                                    });
                            @endphp
                            @if ($mediaItems->count() > 0)
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <div class="image-list">
                                                @foreach ($mediaItems as $media)
                                                    <div class="image-list-detail">
                                                        <div class="position-relative">
                                                            <img src="{{ $media->getUrl() }}" id="{{ $media->id }}"
                                                                alt="Meta Image" class="image-list-item">
                                                            <div class="close-icon">
                                                                <i data-feather="x"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @endisset
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="create-blog-min-box sticky-box">
            <div class="row g-sm-4 g-3">
                <div class="col-12">
                    <div class="card tab2-card">
                        <div class="card-header">
                            <h5>{{ __('static.publish') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="submit" name="save" class="btn btn-primary">
                                    <i class="ri-save-line"></i> {{ __('static.save') }}
                                </button>
                                <button type="submit" name="save_and_exit" class="btn btn-primary spinner-btn">
                                    <i class="ri-expand-left-line"></i>{{ __('static.save_exit') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card tab2-card">
                        <div class="card-header">
                            <h5>{{ __('static.blog.additional_info') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-12" for="categories">{{ __('static.blog.category') }}<span>
                                        *</span> </label>
                                <div class="col-12 error-div select-dropdown">
                                    <select id="blog_categories" class="select-2 form-control" id="categories[]"
                                        search="true" name="categories[]"
                                        data-placeholder="{{ __('static.categories.select-categories') }}" multiple>
                                        <option></option>
                                        @foreach ($categories as $key => $value)
                                            <option value="{{ $key }}" {{ (is_array(old('categories')) && in_array($key, old('categories'))) || (isset($default_categories) && in_array($key, $default_categories)) ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('categories')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                            <label class="col-12" for="tags[]">{{ __('static.blog.tags') }}</label>
                            <div class="col-12 error-div select-dropdown">
                                <select id="tags[]" class="select-2 select-search form-control" search="true"
                                    data-placeholder="{{ __('static.tag.select_tags') }}" name="tags[]"
                                    multiple="multiple">
                                    <option></option>
                                    @foreach ($tags as $key => $value)
                                        <option value="{{ $key }}" {{ (is_array(old('tags')) && in_array($key, old('tags'))) || (isset($default_tags) && in_array($key, $default_tags)) ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tags')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card tab2-card">
                        <div class="card-header">
                            <h5>{{ __('static.status') }}</h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="form-group row">
                                <label for="meta_image" class="col-12">{{ __('static.status') }}</label>
                                <div class="col-12">
                                    <div class="editor-space status-chechbox">
                                        <label class="switch switch-letter">
                                            @if (isset($blog))
                                                <input class="form-control" type="hidden" name="status" value="1">
                                                <input class="form-check-input" type="checkbox" name="status" id=""
                                                    value="0" {{ $blog->status ? 'checked' : '' }}>
                                            @else
                                                <input class="form-control" type="hidden" name="status" value="1" checked>
                                                <input class="form-check-input" type="checkbox" name="status" id=""
                                                    value="0" >
                                            @endif
                                           <span class="box-1">{{ __('static.active') }}</span>
                                            <span class="box-2">{{ __('static.deactivate') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="form-group row">
                                <label for="meta_image" class="col-12">{{ __('static.service.is_featured') }}</label>
                                <div class="col-12">
                                    <div class="editor-space status-chechbox">
                                        <label class="switch switch-letter">
                                            @if (isset($blog))
                                                <input class="form-control" type="hidden" name="is_featured" value="1">
                                                <input class="form-check-input" type="checkbox" name="is_featured" id=""
                                                    value="0" {{ $blog->is_featured ? 'checked' : '' }}>
                                            @else
                                                <input class="form-control" type="hidden" name="is_featured" value="1" checked>
                                                <input class="form-check-input" type="checkbox" name="is_featured" id=""
                                                    value="0" >
                                            @endif
                                             <span class="box-1">{{ __('static.active') }}</span>
                                            <span class="box-2">{{ __('static.deactivate') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        (function ($) {
            "use strict";
            $(document).ready(function () {
                $("#blogForm").validate({
                    ignore: [],
                    rules: {
                        "title": "required",
                        "description": "required",
                        "content": "required",
                        "meta_title": "required",
                        "meta_description": "required",
                        "discount": "required",
                        "image[]": {
                            required: isBlogImages,
                            accept: "image/jpeg, image/png"
                        },
                        "meta_image": {
                            required: isBlogImages,
                            accept: "image/jpeg, image/png"
                        },
                        "categories[]": "required",
                    },
                    messages: {
                        "image[]": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                        "meta_image": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });

                function isBlogImages() {
                    @if(isset($blog->media) && !$blog->media->isEmpty())
                        return false;
                    @else
                        return true;
                    @endif
                }

                function isBlogMetaImage() {
                    @if(isset($blog->meta_image) && !$blog->meta_image->isEmpty())
                        return false;
                    @else
                        return true;
                    @endif
                }
            });
        })(jQuery);
    </script>
@endpush
