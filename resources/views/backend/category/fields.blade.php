    <div class="form-group row">
        <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
        <div class="col-md-10">
            <ul class="language-list">
                @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                    @if(isset($cat))
                        <li>
                            <a href="{{ route('backend.category.edit', ['category' => $cat->id, 'locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i data-feather="arrow-up-right"></i></a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('backend.category.index', ['locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i data-feather="arrow-up-right"></i></a>
                        </li>
                    @endif
                @empty
                    <li>
                        <a href="{{ route('backend.category.edit', ['category' => $cat->id, 'locale' => Session::get('locale', 'en')]) }}" class="language-switcher active" target="blank"><img src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i data-feather="arrow-up-right"></i></a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>


<input type="hidden" name="locale" value="{{ request('locale') }}">
<input type="hidden" name="category_type" value="service">
<div class="form-group row">
    <label class="col-md-2" for="title">{{ __('static.title') }} ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" name="title" placeholder="{{ __('static.categories.enter_title') }} ({{ request('locale', app()->getLocale()) }})" value="{{ isset($cat->title) ? $cat->getTranslation('title', request('locale', app()->getLocale())) : old('title') }}">
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
    <label for="description" class="col-md-2">{{ __('static.categories.description') }} ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <textarea class="form-control" placeholder="{{ __('static.categories.enter_description') }} ({{ request('locale', app()->getLocale()) }})" rows="4" name="description" cols="50">{{ isset($cat) ? $cat->getTranslation('description', request('locale', app()->getLocale())) : old('description') }}</textarea>
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
    <label class="col-md-2" for="commission">{{ __('static.categories.commission') }}<span> *</span></label>
    <div class="col-md-10">
        <div class="input-group mb-3 flex-nowrap">
            <div class="w-100 percent">
                <input class='form-control' id="commission" type="number" name="commission" min="1" value="{{ $cat->commission ?? old('commission') }}" placeholder="{{ __('static.categories.enter_commission') }}" oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
            </div>
            <span class="input-group-text">%</span>
            @error('commission')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="parent_id">{{ __('static.categories.parent') }}</label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control" name="parent_id" data-placeholder="{{ __('static.categories.parent_category') }}">
            <option class="select-placeholder" value=""></option>
            @foreach ($allparent as $key => $option)
                @if ($key != @$cat->id)
                    <option class="option" value="{{ $key }}" @if (old('parent_id', isset($cat) ? $cat->parent_id : '') == $key) selected @endif>{{ $option }}</option>
                @endif
            @endforeach
        </select>
        @error('parent_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="image" class="col-md-2">{{ __('static.provider.image') }} ({{ request('locale', app()->getLocale()) }})</label>
    <div class="col-md-10">
        <input class='form-control' type="file" accept=".jpg, .png, .jpeg" id="image" name="image">
        @error('image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if (isset($cat))
    @php
        $locale = request('locale');
        $mediaItems = $cat->getMedia('image')->filter(function ($media) use ($locale) {
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
                                        alt="Service Category Image" class="image-list-item">
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
    <label class="col-md-2" for="zones">{{ __('static.zone.zones') }}<span> *</span> </label>
    <div class="col-md-10 error-div select-dropdown">
        <select id="blog_zones" class="select-2 form-control disable-all" id="zones[]" search="true" name="zones[]" data-placeholder="{{ __('static.zone.select-zone') }}" multiple>
            <option></option>
            <option value="all">{{ __('static.report.select_all') }}</option>
            @foreach ($zones as $key => $value)
                <option value="{{ $key }}" {{ (is_array(old('zones')) && in_array($key, old('zones'))) || (isset($default_zones) && in_array($key, $default_zones)) ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
        </select>
        @error('zones')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="status">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($cat))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1" {{ $cat->status ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1" checked>
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>
@push('js')
    <script src="{{ asset('admin/js/jstree.min.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#categoryForm").validate({
                    ignore: [],
                    rules: {
                        "title": "required",
                        "description": "required",
                        "commission": "required",
                        "content": "required",
                        "zones[]": "required",
                        "image": {
                            accept: "image/jpeg, image/png"
                        },
                    },
                    messages: {
                        "image": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });
            });
            var tree_custom = {
                init: function() {
                    $('#treeBasic').jstree({
                        'core': {
                            'themes': {
                                'responsive': false,
                            },
                        },
                        'types': {
                            'default': {
                                'icon': 'ti-gallery'
                            },
                            'file': {
                                'icon': 'ti-file'
                            }
                        },
                        "search": {
                            "case_insensitive": true,
                            "show_only_matches": true
                        },
                        'plugins': ['types', 'search']
                    });

                    $('#search').keyup(function() {
                        $('#treeBasic').jstree('search', $(this).val());
                    });

                    $(document).on('click', '.edit-icon', function(e) {
                        var url = $(this).data('url')
                        window.location.href = url;
                    });

                    $(document).on('click', '.edit-child', function(e) {
                        var url = $(this).data('url')
                        window.location.href = url;
                    });
                }
            };
            $(document).ready(function() {
                tree_custom.init();

                setTimeout(function() {
                    $('.jstree-loader').fadeOut('slow');
                    $('#treeBasic').show();
                }, 500);
            });
            $('.disable-all').on('change', function() {
                const $currentSelect = $(this);
                const selectedValues = $currentSelect.val();
                const allOption = "all";

                if (selectedValues && selectedValues.includes(allOption)) {

                    $currentSelect.val([allOption]);
                    $currentSelect.find('option').not(`[value="${allOption}"]`).prop('disabled', true);
                } else {

                    $currentSelect.find('option').prop('disabled', false);
                }
            });
        })(jQuery);
    </script>
@endpush
