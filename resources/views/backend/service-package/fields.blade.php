@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush
@use('app\Helpers\Helpers')

    <div class="form-group row">
        <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
        <div class="col-md-10">
            <ul class="language-list">
                @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                    @if(isset($service_package))
                        <li>
                            <a href="{{ route('backend.service-package.edit', ['service_package' => $service_package->id, 'locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('backend.service-package.create', ['locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @endif
                @empty
                    <li>
                        <a href="{{ route('backend.service-package.edit', ['service_package' => $service_package->id, 'locale' => Session::get('locale', 'en')]) }}"
                            class="language-switcher active" target="blank"><img
                                src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i
                                data-feather="arrow-up-right"></i></a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

<input type="hidden" name="locale" value="{{ request('locale') }}">
<div class="form-group row">
    <label for="image" class="col-md-2">{{ __('static.categories.image') }}
        ({{ request('locale', app()->getLocale()) }})</label>
    <div class="col-md-10">
        <input class='form-control' type="file" accept=".jpg, .png, .jpeg" id="image" name="image[]">
        @error('image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if (isset($service_package))
    @php
        $locale = request('locale');
        $mediaItems = $service_package->getMedia('image')->filter(function ($media) use ($locale) {
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
                                        alt="Service Package Image" class="image-list-item">
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
    <label class="col-md-2" for="title">{{ __('static.title') }}
        ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10">
        <div class="input-copy-box">
            <input class='form-control' type="text" id="title" name="title"
                value="{{ isset($service_package) ? $service_package->getTranslation('title', request('locale', app()->getLocale())) : old('title') }}"
                placeholder="{{ __('static.service_package.enter_title') }} ({{ request('locale', app()->getLocale()) }})">
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
</div>
@hasrole('admin')
    <div class="form-group row">
        <label class="col-md-2" for="provider_id">{{ __('static.service.provider') }}<span> *</span></label>
        <div class="col-md-10 error-div select-service-dropdown">
            <select class="select-2 form-control user-dropdown" id="provider_id" name="provider_id"
                data-placeholder="{{ __('static.service.select_provider') }}">
                <option class="select-placeholder" value=""></option>
                @foreach ($providers as $key => $provider)
                    <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                        image="{{ $provider->getFirstMedia('image')?->getUrl() }}"
                        {{ old('provider_id', isset($service_package) ? $service_package->provider_id : '') == $provider->id ? 'selected' : '' }}>
                        {{ $provider->name }}
                    </option>
                @endforeach
            </select>
            @error('provider_id')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
@endhasrole

@hasrole('provider')
    <input type="hidden" name="provider_id" value="{{ auth()->user()->id }}">
@endhasrole

<div class='form-group row'>
    <label class="col-md-2" for="service_id">{{ __('static.service_package.services') }}
        <span> *</span> </label>
    <div class="col-md-10 error-div select-dropdown">
        <select id="services" class="select-2 form-control user-dropdown disable-all" search="true" name="service_id[]"
            data-placeholder="{{ __('static.service.select_services') }}" multiple>
            <option value=""></option>
        </select>
        @error('service_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="price">{{ __('static.service_package.price') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <div class="input-group mb-3 flex-nowrap">
            <span class="input-group-text">{{ Helpers::getSettings()['general']['default_currency']->symbol }}</span>
            <div class="w-100">
                <input class='form-control' type="number" id="price" name="price" min="1"
                    value="{{ isset($service_package->price) ? $service_package->price : old('price') }}"
                    placeholder="{{ __('static.service_package.enter_price') }}">
                @error('price')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="discount">{{ __('static.service_package.discount') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <div class="input-group mb-3 flex-nowrap">
            <div class="w-100 percent">
                <input class='form-control' id="discount" type="number" name="discount" min="1"
                    value="{{ $service_package->discount ?? old('discount') }}"
                    placeholder="{{ __('static.service_package.enter_discount') }}"
                    oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
            </div>
            <span class="input-group-text">%</span>
            @error('discount')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group row">
    <label for="description" class="col-md-2">{{ __('static.service_package.description') }}
        ({{ request('locale', app()->getLocale()) }})</label>
    <div class="col-md-10">
        <textarea class="form-control" rows="4" name="description" id="description"
            placeholder="{{ __('static.tag.enter_description') }} ({{ request('locale', app()->getLocale()) }})"
            cols="50">{{ isset($service_package) ? $service_package->getTranslation('description', request('locale', app()->getLocale())) : old('description') }}</textarea>
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

<div class="form-group row flatpicker-calender">
    <label class="col-md-2" for="start_end_date">{{ __('static.service_package.date') }}<span> *</span> </label>
    <div class="col-md-10">
        @if (isset($service_package))
            <input class="form-control" id="date-range"
                value="{{ \Carbon\Carbon::parse(@$service_package->started_at)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(@$service_package->ended_at)->format('d-m-Y') }}"
                name="start_end_date" placeholder="{{ __('static.service_package.select_date') }}">
        @else
            <input class="form-control" id="date-range" name="start_end_date"
                placeholder="{{ __('static.service_package.select_date') }}">
        @endif
        @error('start_end_date')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="bg_color">{{ __('static.service.bg_color') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control" id="bg_color" name="bg_color"
            data-placeholder="{{ __('static.service.select_bg_color') }}">
            <option class="select-placeholder" value=""></option>
            @foreach (['primary', 'secondary', 'info', 'success', 'warning', 'danger'] as $bg_color)
                <option class="option" value="{{ $bg_color }}"
                    {{ old('bg_color', isset($service_package) ? $service_package->bg_color : '') == $bg_color ? 'selected' : '' }}>
                    {{ ucfirst($bg_color) }}
                </option>
            @endforeach
        </select>
        @error('bg_color')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="hexa_code">{{ __('static.hexa_code') }}<span> *</span></label>
    <div class="col-md-10">
        <div class="d-flex align-items-center gap-2">
            <input class="form-control" type="color" name="hexa_code" id="hexa_code"
                value="{{ isset($service_package->hexa_code) ? $service_package->hexa_code : old('hexa_code') }}"
                placeholder="{{ __('static.service_package.enter_color') }}">
            <span class="color-picker">#000</span>
        </div>
        @error('hexa_code')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.service_package.is_featured') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($service_package))
                    <input class="form-control" type="hidden" name="is_featured" value="0">
                    <input class="form-check-input" type="checkbox" name="is_featured" id=""
                        value="1" {{ $service_package->is_featured ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="is_featured" value="0">
                    <input class="form-check-input" type="checkbox" name="is_featured" id=""
                        value="1">
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($service_package))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $service_package->status ? 'checked' : '' }}>
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
@push('js')
    <script src="{{ asset('admin/js/flatpickr.js') }}"></script>
    <script src="{{ asset('admin/js/custom-flatpickr.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#servicepackageForm").validate({
                    ignore: [],
                    rules: {
                        "provider_id": "required",
                        "title": "required",
                        "provider_id": "required",
                        "start_end_date": "required",
                        "service_id[]": "required",
                        "price": "required",
                        "discount": "required",
                        "bg_color": "required",
                        "image[]": {
                            accept: "image/jpeg, image/png"
                        },
                        "hexa_code": "required",
                    }
                });

                // Handle provider change on page load
                var initialProviderID = $('input[name="provider_id"]').val() || $('select[name="provider_id"]')
                    .val();
                if (initialProviderID) {
                    loadServices(initialProviderID);
                }
                @isset($service_package)
                    var selectedServices =
                        {{ json_encode($service_package?->services?->pluck('id')?->toArray() ?? []) }};
                    loadServices(initialProviderID, selectedServices);
                @endisset

                // Handle provider change
                $('select[name="provider_id"]').on('change', function() {
                    var providerID = $(this).val();
                    loadServices(providerID);
                });

                const colorInput = $('#hexa_code');
                const colorPickerSpan = $('.color-picker');

                // Initialize span with the initial color input value
                colorPickerSpan.text(colorInput.val());

                // Update span text content when the color input value changes
                colorInput.on('input', function() {
                    colorPickerSpan.text($(this).val());
                });
            });
            // Function to load services based on the selected provider
            function loadServices(providerID, selectedServices) {
                let url = "{{ route('backend.get-provider-services', '') }}";

                if (providerID) {
                    $.ajax({
                        url: url,
                        type: "GET",
                        data: {
                            provider_id: providerID,
                        },
                        success: function(data) {
                            $('#services').empty();

                            let selectAllOption = new Option("Select All", "all");
                            $('#services').append(selectAllOption);

                            $.each(data, function(id, optionData) {
                                var option = new Option(optionData.title, optionData.id);

                                if (optionData.media.length > 0) {
                                    var imageUrl = optionData.media[0].original_url
                                    $(option).attr("image", imageUrl);
                                }

                                if (selectedServices && selectedServices.includes(String(optionData
                                        .id))) {
                                    $(option).prop("selected", true);
                                }
                                $('#services').append(option);
                            });
                            $('#services').val(selectedServices).trigger('change');
                        },
                        error: function(xhr) {
                            toastr.error("Error loading services:", xhr.responseText);
                        }
                    });
                }
            }
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
            function isServiceImages() {
                @if (isset($service_package?->media) && !$service_package?->media?->isEmpty())
                    return false;
                @else
                    return true;
                @endif
            }
        })(jQuery);
    </script>
@endpush
