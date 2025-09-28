@use('App\Models\Zone')
@use('app\Helpers\Helpers')
@use('App\Enums\RoleEnum')

@php
    $zones = Zone::where('status', true)->pluck('name', 'id');
    $providers = Helpers::getProviders()->get();
    $settings = Helpers::getSettings();
    $user = auth()->user();
    $roleName = $user->roles->pluck('name')?->first();
@endphp

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/flatpickr.min.css') }}">
@endpush

<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('static.advertisement.create') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-sm-4 g-3">
                    <div class="col-xl-8">
                            <div class="form-group row">
                                <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
                                <div class="col-md-10">
                                    <ul class="language-list">
                                        @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                                            @if(isset($advertisement))
                                                <li>
                                                    <a href="{{ route('backend.advertisement.edit', ['advertisement' => $advertisement->id, 'locale' => $lang->locale]) }}"
                                                        class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                        target="_blank"><img
                                                            src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                            alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                            data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="{{ route('backend.advertisement.create', ['locale' => $lang->locale]) }}"
                                                        class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                                        target="_blank"><img
                                                            src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                                            alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                                            data-feather="arrow-up-right"></i></a>
                                                </li>
                                            @endif
                                        @empty
                                            <li>
                                                <a href="{{ route('backend.advertisement.edit', ['advertisement' => $advertisement->id, 'locale' => Session::get('locale', 'en')]) }}"
                                                    class="language-switcher active" target="blank"><img
                                                        src="{{ asset('admin/images/flags/LR.png') }}"
                                                        alt="">English<i data-feather="arrow-up-right"></i></a>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                            @if (Helpers::getCurrentRoleName())
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="provider_id">{{ __('static.wallet.select_provider') }}<span>
                                            *</span></label>
                                    <div class="col-md-10 error-div select-dropdown">
                                        <select class="select-2 form-control user-dropdown" name="provider_id"
                                            id="providerDropdown"
                                            data-placeholder="{{ __('static.wallet.select_provider') }}" required>
                                            <option class="select-placeholder" value=""></option>

                                            @if ($roleName === RoleEnum::PROVIDER)
                                                <option value="{{ $user->id }}"  sub-title="{{ $user->email }}"
                                                    image="{{ $user->getFirstMedia('image')?->getUrl() }}"
                                                    selected>
                                                    {{ $user->name }}
                                                </option>
                                            @elseif ($roleName === RoleEnum::ADMIN)
                                                {{-- Show all providers --}}
                                                @foreach ($providers as $provider)
                                                    <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                                                        image="{{ $provider->getFirstMedia('image')?->getUrl() }}"
                                                        @if (old('provider_id', isset($advertisement) ? $advertisement->provider_id : '') == $provider->id) selected @endif>
                                                        {{ $provider->name }}
                                                    </option>
                                                @endforeach
                                            @endif  
                                        </select>
                                        @error('provider_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                        <input type="hidden" name="locale" value="{{ request('locale') }}">
                        <div class="form-group row">
                            <label class="col-md-2" for="type">{{ __('static.advertisement.type') }}<span>
                                    *</span></label>
                            <div class="col-md-10 error-div advertisement-box">
                                <select class="select-2 form-control advertisement_type" name="type" id="type"
                                    data-placeholder="{{ __('static.advertisement.select_type') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($advertisementType as $key => $option)
                                        <option class="option" value="{{ $key }}"
                                            @if (isset($advertisement)) @if ($key == $advertisement->type) selected @endif
                                            @endif>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2" for="type">{{ __('static.advertisement.screen') }}<span>
                                    *</span></label>
                            <div class="col-md-10 error-div advertisement-box">
                                <select class="select-2 form-control advertisement_type" name="screen" id="screen"
                                    data-placeholder="{{ __('static.advertisement.select_screen') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($advertisementScreen as $key => $option)
                                        <option class="option" value="{{ $key }}"
                                            @if (isset($advertisement)) @if ($key == $advertisement->screen) selected @endif
                                            @endif>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <span class="help-text" style="display: none;">
                                    Selecting this screen for advertisement will incur a charge of <span
                                    id="screen-price"></span> per day.
                                </span>
                                @error('screen')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row" id="banner_type_div">
                            <label class="col-md-2" for="type">{{ __('static.advertisement.banner_type') }}<span>
                                    *</span></label>
                            <div class="col-md-10 error-div advertisement-box">
                                <select class="select-2 form-control advertisement_type" name="banner_type"
                                    id="banner_type"
                                    data-placeholder="{{ __('static.advertisement.select_banner_type') }}">
                                    <option class="select-placeholder" value=""></option>
                                    @foreach ($advertisementBannerType as $key => $option)
                                        <option class="option" value="{{ $key }}"
                                            @if (isset($advertisement)) @if ($key == $advertisement->banner_type) selected @endif
                                            @endif>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('banner_type')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row" id="video_upload" style="display: none;">
                            <label class="col-md-2" for="video_link">{{ __('static.advertisement.video_link') }}
                                ({{ request('locale', app()->getLocale()) }})<span>
                                    *</span></label>
                            <div class="col-md-10 input-copy-box">
                                <input class="form-control" id="video_link" type="text" name="video_link"
                                    value="{{ isset($advertisement->video_link) ? $advertisement->getTranslation('video_link', request('locale', app()->getLocale())) : old('video_link') }}"
                                    placeholder="{{ __('static.advertisement.enter_video_link') }} ({{ request('locale', app()->getLocale()) }})">
                                @error('video_link')
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

                        <div class="form-group row" id="image_upload" style="display: none;">
                            <label for="image" class="col-md-2">{{ __('static.advertisement.image') }}
                                ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
                            <div class="col-md-10">
                                <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple>
                                @error('images')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <p id="image-price-text"
                                    style="display: none; font-weight: bold; color: green; margin-top: 5px;"></p>
                            </div>
                        </div>

                        @isset($advertisement->media)
                            @if ($advertisement->type === 'banner')
                                @php
                                    $locale = request('locale');
                                    $mediaItems = $advertisement
                                        ->getMedia('image')
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
                                                                <img src="{{ $media->getUrl() }}"
                                                                    id="{{ $media->id }}" alt="Banner Image"
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
                        @endisset

                        <div class="form-group row" style="display: none;" id="service_selection_section">
                            <label class="col-md-2" for="service_id">{{ __('static.service.services') }}<span>
                                    *</span></label>
                            <div class="col-md-10 error-div">
                                <select class="select-2 form-control user-dropdown" id="service_id"
                                    name="service_id[]"
                                    data-placeholder="{{ __('static.additional_service.select_service') }}" multiple>
                                    <option class="select-placeholder" value=""></option>

                                </select>
                                @error('service_id')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <p id="service-price-text"
                                    style="display: none; font-weight: bold; color: green; margin-top: 5px;"></p>
                            </div>
                        </div>

                        <div class="form-group row flatpicker-calender select-date">
                            <label class="col-md-2" for="start_end_date">{{ __('static.advertisement.select_date') }} <span
                                    class="required-span">*</span></label>
                            <div class="col-md-10">
                            @if (isset($advertisement))
                                <input class="form-control" id="date-range"
                                    value="{{ \Carbon\Carbon::parse($advertisement->start_date)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($advertisement->end_date)->format('d-m-Y') }}"
                                    name="start_end_date" placeholder="Select Date..">
                            @else
                                <input class="form-control" id="date-range" name="start_end_date" placeholder="Select Date..">
                            @endif

                                @error('start_end_date')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <p class="help-text" style="display: none;">
                                    <span id="date-price"></span>
                                </p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2" for="zones">{{ __('static.zone.zones') }}<span> *</span>
                            </label>
                            <div class="col-md-10 error-div select-dropdown">

                                <select id="blog_zones" class="select-2 form-control" id="zone" search="true"
                                    name="zone" data-placeholder="{{ __('static.zone.select-zone') }}">
                                    <option></option>
                                    @foreach ($zones as $key => $value)
                                        <option value="{{ $key }}"
                                            @if (isset($advertisement)) @if ($key == $advertisement->zone) selected @endif
                                            @endif>
                                            {{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('zone')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button id='submitBtn' type="submit"
                                class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="advertisement-list-box">
                            <div class="total-box">
                                <h4>{{ __('static.advertisement.total_amount') }}  :</h4>
                                <h6>{{ Helpers::getDefaultCurrency()->symbol }}<span id="total-price">0</span></h6>
                            </div>
                            <input type="hidden" value="" id="total-val" name="price">
                            <ul class="advertisement-list">
                                <li id="home-screen-price">{{ __('static.advertisement.home_screen_price') }}  : <span>{{ Helpers::getDefaultCurrency()->symbol }}{{ $settings['advertisement']['home_screen_price'] }}</span></li>
                                <li id="home-screen-price">{{ __('static.advertisement.category_screen_price') }} : <span>{{ Helpers::getDefaultCurrency()->symbol }}{{ $settings['advertisement']['category_screen_price'] }}</span></li>
                                <li style="display :none" id="total-no-of-images-li">{{ __('static.advertisement.total_images_video') }} : <h6><span id="total-no-of-images" ></span></h6></li>
                                <li style="display :none" id="total-no-of-services-li">{{ __('static.advertisement.total_services') }} : <h6><span id="total-no-of-services" ></span></h6></li>
                                <li id="total-no-of-days-li">{{ __('static.advertisement.total_days') }} : <h6><span id="total-no-of-days"></span></h6></li>
                                <li class="total">{{ __('static.advertisement.total') }} : <h6>{{ Helpers::getDefaultCurrency()->symbol }}<span id="total">0</span></h6></li>
                            </ul>
                        </div>
                        <p class="wallet-deduction-message">{{ __('static.advertisement.note') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="{{ asset('admin/js/flatpickr.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                var isImagesRequired = <?php echo isset($advertisement->media) && !$advertisement->media->isEmpty() ? 'false' : 'true'; ?>;


                $("#advertisementForm").validate({
                    ignore: [],
                    rules: {

                        "type": "required",
                        "screen": "required",
                        "provider_id": "required",
                        "start_end_date": "required",
                        "zone": "required",
                        "images[]": {
                            required: setImagesRule
                        },
                        "service_id[]": {
                            required: setServicesRule
                        },
                        'video_link': {
                            required: setVideoRule
                        }
                    },
                });

                function updateAdvertisementForm() {
                    let adType = $('#type').val();

                    if (adType === 'banner') {

                        $('#service_selection_section').hide();
                        $('#image-price-text').show();
                        $('#service-price-text').hide();

                        $('.help-text').hide();
                    } else if (adType === 'service') {

                        $('#service_selection_section').show();
                        $('#image-price-text').hide();
                        $('#service-price-text').show();
                        $('.help-text').hide();

                    }

                    updateTotalPrice();
                }

                function updateBannerUpload() {
                    let bannerType = $('#banner_type').val();
                    if (bannerType === 'image') {
                        $('#image_upload').show();
                        $('#video_upload').hide();
                    } else if (bannerType === 'video') {
                        $('#image_upload').hide();
                        $('#video_upload').show();
                    }
                }

                function updateTotalPrice() {

                    let adType = $('#type').val();
                    let screenPrices = {
                        home: parseFloat("{{ $settings['advertisement']['home_screen_price'] }}"),
                        category: parseFloat("{{ $settings['advertisement']['category_screen_price'] }}"),
                    };

                    let selectedScreen = $('#screen').val();
                    let screenPrice = screenPrices[selectedScreen] || 0;
                    let selectedDays = 1;
                    let totalPrice = 0;


                    let dateRange = $('#date-range').val().split(" to ");
                    if (dateRange.length === 2) {
                        let startParts = dateRange[0].split("-");
                        let endParts = dateRange[1].split("-");
                        let startDate = new Date(startParts[2], startParts[1] - 1, startParts[0]);
                        let endDate = new Date(endParts[2], endParts[1] - 1, endParts[0]);
                        selectedDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                        $('#total-no-of-days').text(selectedDays);
                    }


                    if (adType === 'banner') {
                        $('#banner_type_div').show();

                        let imageCount;
                        let bannerType = $('#banner_type').val();

                        if (bannerType === 'image') {
                            $('#image_upload').show();
                            $('#video_upload').hide();

                            let existingImagesCount = $('.image-list-item').length;
                            let newImagesCount = $('#images')[0].files.length;
                            imageCount = existingImagesCount > 0 ? existingImagesCount : newImagesCount;

                        } else if (bannerType === 'video') {
                            $('#image_upload').hide();
                            $('#video_upload').show();
                            imageCount = 1;
                        }

                        if (screenPrice > 0 && imageCount > 0 && selectedDays > 0) {

                            totalPrice = screenPrice * imageCount * selectedDays;


                            $('#total-no-of-images-li').show();
                            $('#total-no-of-images').text(imageCount);
                            $('#total-no-of-services-li').hide();
                            $('#total-price').text(totalPrice);
                            $('#total').text(totalPrice);
                            $('#total-val').val(totalPrice);
                        }
                    }

                    if (adType === 'service') {
                        $('#banner_type_div').hide();
                        $('#image_upload').hide();
                        $('#video_upload').hide();

                        let serviceCount = $('#service_id').val().length;
                        if (screenPrice > 0 && serviceCount > 0 && selectedDays > 0) {
                            totalPrice = screenPrice * serviceCount * selectedDays;
                            $('#total-no-of-services-li').show();
                            $('#total-no-of-services').text(serviceCount);
                            $('#total-no-of-images-li').hide();
                            $('#total-price').text(totalPrice);
                            $('#total').text(totalPrice);
                            $('#total-val').val(totalPrice);
                        } else {

                        }
                    }
                }


                $('#type').change(updateAdvertisementForm);
                $('#images').change(updateTotalPrice);
                $('#service_id').change(updateTotalPrice);
                $('#date-range').change(updateTotalPrice);
                $('#banner_type').change(updateBannerUpload);

                updateAdvertisementForm();

                var initialProviderID = $('input[name="provider_id"]').val() || $('select[name="provider_id"]')
                    .val();

                if (initialProviderID) {
                    loadServices(initialProviderID);
                }
                @isset($advertisement)
                    var selectedServices = {!! json_encode($advertisement->services->where('id', '!=', $advertisement->id)->pluck('id')->toArray() ?? []) !!};
                    loadServices(initialProviderID, selectedServices);
                @endisset

                $('select[name="provider_id"]').on('change', function() {
                    var providerID = $(this).val();
                    loadServices(providerID);
                });

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
                                $('#service_id').empty();
                                $.each(data, function(id, optionData) {
                                    var option = new Option(optionData.title, optionData
                                        .id);
                                    if (optionData.media.length > 0) {
                                        var imageUrl = optionData.media[0].original_url
                                        $(option).attr("image", imageUrl);
                                    }

                                    if (selectedServices && selectedServices.includes(
                                            String(optionData.id))) {
                                        $(option).prop("selected", true);
                                    }

                                    $('#service_id').append(option);
                                });

                                $('#service_id').val(selectedServices).trigger('change');
                            },
                        });
                    }
                }
                var isImagesRequired = <?php echo isset($advertisement->media) && !$advertisement->media->isEmpty() ? 'false' : 'true'; ?>;

                function setImagesRule() {

                    if ($('#type').val() === 'banner' && $('#banner_type').val() === 'image' &&
                        isImagesRequired) {
                        return true;
                    } else {
                        return false
                    }
                }

                function setVideoRule() {
                    if ($('#type').val() === 'banner' && $('#banner_type').val() === 'video') {
                        return true;
                    } else {
                        return false;
                    }
                }

                function setServicesRule() {
                    return $('#type').val() === 'service';
                }

                var maxImages = "{{ $settings['advertisement']['max_image_uploads'] }}";

                $('#images').on('change', function() {
                    if (this.files.length > maxImages) {
                        toastr.error("You can only upload a maximum of " + maxImages + " images.");
                        $(this).val('');

                    }
                });
                const maxBookingDays = {{ Helpers::getsettings()['default_creation_limits']['max_advertisement_days'] ?? 30 }};
                const today = new Date();
                const maxDate = new Date();
                maxDate.setDate(today.getDate() + maxBookingDays);

                flatpickr("#date-range", {
                    mode: "range",
                    maxDate: maxDate,
                    dateFormat: "d-m-Y"
                });


            });
        })(jQuery);
    </script>
@endpush
