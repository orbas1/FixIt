@use('App\Enums\PageTypeEnum')
<input type="hidden" name="locale" value="{{ request('locale') }}">

<div class="row">
    <div class="col-xl-8">
        <div class="row g-sm-4 g-3">
            <div class="col-12">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>{{ __('static.page.create') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('backend.page.store') }}" id="pageForm" method="POST"
                            enctype="multipart/form-data">
                                <div class="form-group row">
                                    <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
                                    <div class="col-md-10">
                                        <ul class="language-list">
                                            @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                                                @if(isset($page))
                                                    <li>
                                                        <a href="{{ route('backend.page.edit', ['page' => $page->id, 'locale' => $lang->locale]) }}"
                                                            class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                            target="_blank"><img
                                                                src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                                alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                                data-feather="arrow-up-right"></i></a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <a href="{{ route('backend.page.create', ['locale' => $lang->locale]) }}"
                                                            class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                            target="_blank"><img
                                                                src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                                alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                                data-feather="arrow-up-right"></i></a>
                                                    </li>
                                                @endif
                                            @empty
                                                <li>
                                                    <a href="{{ route('backend.page.edit', ['page' => $page->id, 'locale' => Session::get('locale', 'en')]) }}"
                                                        class="language-switcher active" target="blank"><img
                                                            src="{{ asset('admin/images/flags/LR.png') }}"
                                                            alt="">English<i data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>

                            <div class="form-group row">
                                <label class="col-md-2" for="app_type">{{ __('static.page.app_type') }}<span> *</span></label>
                                <div class="col-md-10 error-div">
                                    <select class="select-2 form-control" id="app_type" name="app_type" data-placeholder="{{ __('static.page.select_app_type') }}">
                                        <option class="select-placeholder" value=""></option>
                                        @foreach ([PageTypeEnum::USER => 'User', PageTypeEnum::PROVIDER => 'Provider'] as $key => $option)
                                            <option class="option" value="{{ $key }}"
                                                @if (old('app_type', isset($page) ? $page->app_type : '') == $key) selected @endif>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('app_type')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-md-2" for="title">{{ __('static.title') }}
                                    ({{ request('locale', app()->getLocale()) }})<span>
                                        *</span></label>
                                <div class="col-md-10 input-copy-box">
                                    <input class='form-control' type="text" name="title" id="title"
                                        value="{{ isset($page->title) ? $page->getTranslation('title', request('locale', app()->getLocale())) : old('title') }}"
                                        placeholder="{{ __('static.page.enter_title') }} ({{ request('locale', app()->getLocale()) }})">
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
                                <label for="image" class="col-md-2">{{ __('static.page.content') }}
                                    ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                                <div class="col-md-10 error-div">
                                    <textarea class="summary-ckeditor" id="content" name="content" cols="65" rows="5">{{ isset($page->content) ? $page->getTranslation('content', request('locale', app()->getLocale())) : old('content') }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="app_icon" class="col-md-2">{{ __('static.page.app_icon') }}
                                    ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                                <div class="col-md-10">
                                    <div class="upload-image-box">
                                        <div class="upload-input">
                                            <i class="ri-add-line"></i>
                                            <input class='form-control' type="file" id="app_icon" name="app_icon">
                                            @error('app_icon')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        @if (isset($page))
                                        @php
                                            $locale = request('locale');
                                            $mediaItems = $page->getMedia('app_icon')->filter(function ($media) use ($locale) {
                                                return $media->getCustomProperty('language') === $locale;
                                            });
                                        @endphp
                                        @if ($mediaItems->count() > 0)
                                        <div class="image-list">
                                            @foreach ($mediaItems as $media)
                                                <div class="image-list-detail">
                                                    <div class="position-relative">
                                                        <img src="{{ $media->getUrl() }}"
                                                            id="{{ $media->id }}" alt="App Icon"
                                                            class="image-list-item">
                                                        <div class="close-icon">
                                                            <i data-feather="x"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    @endif
                                    </div>
                                </div>
                            </div>
                            {{-- @csrf
                            @include('backend.page.fields')
                            <div class="footer">
                                <button id='submitBtn' type="submit"
                                    class="btn btn-primary spinner-btn">{{ __('static.submit') }}</button>
                            </div> --}}
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card tab2-card">
                    <div class="card-header">
                        <h5>Search Engine Optimization (SEO)</h5>
                    </div>
                    <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-2" for="meta_title">{{ __('static.page.meta_title') }}
                                    ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                                <div class="col-md-10 input-copy-box">
                                    <input class='form-control' type="text" name="meta_title" id="meta_title"
                                        value="{{ isset($page->meta_title) ? $page->getTranslation('meta_title', request('locale', app()->getLocale())) : old('meta_title') }}"
                                        placeholder="{{ __('static.page.placeholder_meta_title') }} ({{ request('locale', app()->getLocale()) }})">
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

                            <div class = "form-group row">
                                <label for="address" class="col-md-2">{{ __('static.page.meta_descripation') }}
                                    ({{ request('locale', app()->getLocale()) }})</label>
                                <div class="col-md-10 input-copy-box">
                                    <textarea class = "form-control" id="meta_descripation" rows="4"
                                        placeholder="{{ __('static.pages.meta_description') }} ({{ request('locale', app()->getLocale()) }})"
                                        name="meta_description" cols="50">{{ isset($page->meta_description) ? $page->getTranslation('meta_description', request('locale', app()->getLocale())) : old('meta_descripation') }}</textarea>
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
                                <label for="meta_image" class="col-md-2">{{ __('static.page.meta_image') }}
                                    ({{ request('locale', app()->getLocale()) }})</label>
                                <div class="col-md-10">
                                    <div class="upload-image-box">
                                        <div class="upload-input">
                                            <i class="ri-add-line"></i>
                                            <input class='form-control' type="file" id="meta_image" name="meta_image">
                                            @error('meta_image')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        @if (isset($page))
                                            @php
                                                $locale = request('locale');
                                                $mediaItems = $page
                                                    ->getMedia('meta_image')
                                                    ->filter(function ($media) use ($locale) {
                                                        return $media->getCustomProperty('language') === $locale;
                                                    });
                                            @endphp
                                            @if ($mediaItems->count() > 0)
                                            <div class="image-list">
                                                @foreach ($mediaItems as $media)
                                                    <div class="image-list-detail">
                                                        <div class="position-relative">
                                                            <img src="{{ $page->getFirstMedia('meta_image')->original_url }}"
                                                                id="{{ $page->getFirstMedia('meta_image')->id }}"
                                                                alt="{{ $page->getFirstMedia('meta_image')->name }}"
                                                                class="image-list-item">
                                                            <div class="close-icon">
                                                                <i data-feather="x"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- @csrf
                            @include('backend.page.fields')
                            <div class="footer">
                                <button id='submitBtn' type="submit"
                                    class="btn btn-primary spinner-btn">{{ __('static.submit') }}</button>
                            </div> --}}

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="sticky-box">
            <div class="row g-sm-4 g-3">
                <div class="col-12">
                    <div class="card tab2-card">
                        <div class="card-header">
                            <h5>Publish</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="submit" name="save" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Save
                                </button>
                                <button type="submit" name="save_and_exit" class="btn btn-primary spinner-btn">
                                    <i class="ri-expand-left-line"></i>Save and Exit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card tab2-card">
                        <div class="card-header">
                            <h5>Additional Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="meta_image" class="col-12">{{ __('static.status') }}</label>
                                <div class="col-12">
                                    <div class="editor-space status-chechbox">
                                        <label class="switch switch-letter">
                                            @if (isset($page))
                                                <input class="form-control" type="hidden" name="status" value="1">
                                                <input class="form-check-input" type="checkbox" name="status" id="" value="0"
                                                    {{ $page->status ? 'checked' : '' }}>
                                            @else
                                                <input class="form-control" type="hidden" name="status" value="1">
                                                <input class="form-check-input" type="checkbox" name="status" id="" value="0"
                                                    checked>
                                            @endif
                                            <span class="box-1">Active</span>
                                            <span class="box-2">Deactivate</span>
                                        </label>
                                    </div>
                                </div>
                                {{-- <div class="col-12">
                                    <div class="form-group row">

                                        <div class="col-md-10">
                                            <div class="editor-space">
                                                <label class="switch">
                                                    @if (isset($page))
                                                        <input class="form-control" type="hidden" name="status" value="0">
                                                        <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                                                            {{ $page->status ? 'checked' : '' }}>
                                                    @else
                                                        <input class="form-control" type="hidden" name="status" value="0">
                                                        <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                                                            checked>
                                                    @endif
                                                    <span class="switch-state"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-field form-control">
                                        <div class="switch-field-box">
                                            <input type="radio" name="status" id="feature_active" checked
                                                value="1">
                                            <label for="feature_active">Active</label>
                                        </div>
                                        <div class="switch-field-box">
                                            <input type="radio" name="status" id="feature_deactivate"
                                                value="0">
                                            <label for="feature_deactivate">Deactivate</label>
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
</div>

@push('js')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#pageForm").validate({
                    ignore: [],
                    rules: {
                        "title": "required",
                        "app_type": "required",
                        "content": "required",
                        "meta_title": "required",
                        "meta_description": "required",
                    },
                });
            });
            tinymce.init({
                selector: '.summary-ckeditor',
                image_class_list: [{
                    title: 'Responsive',
                    value: 'img-fluid'
                }, ],

                width: '100%',
                height: 350,
                setup: function(editor) {
                    editor.on('init change', function() {
                        editor.save();
                    });
                },
                plugins: [
                    "advlist autolink lists link image charmap print preview anchor",
                    "searchreplace visualblocks code fullscreen",
                    "insertdatetime media table contextmenu paste imagetools"
                ],
                toolbar: [
                    'newdocument | print preview | searchreplace | undo redo  | alignleft aligncenter alignright alignjustify | code',
                    'formatselect fontselect fontsizeselect | bold italic underline strikethrough | forecolor backcolor',
                    'removeformat | hr pagebreak | charmap subscript superscript insertdatetime | bullist numlist | outdent indent blockquote | table'
                ],
                menubar: false,
                image_title: true,
                automatic_uploads: true,
                file_picker_types: 'image',
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                branding: false,
                file_picker_callback: function(cb, value, meta) {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.onchange = function() {
                        var file = this.files[0];

                        var reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = function() {
                            var id = 'blobid' + (new Date()).getTime();
                            var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                            var base64 = reader.result.split(',')[1];
                            var blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);
                            cb(blobInfo.blobUri(), {
                                title: file.name
                            });
                        };
                    };
                    input.click();
                },
                placeholder: 'Enter your text here...',
            });
        })(jQuery);
    </script>
@endpush
