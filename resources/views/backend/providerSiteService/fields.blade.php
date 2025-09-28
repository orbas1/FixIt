@use('app\Helpers\Helpers')
@use('App\Enums\ServiceTypeEnum')
<ul class="nav nav-tabs tab-coupon" id="servicetab" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ session('active_tab') != null ? '' : 'show active' }}" id="general-tab" data-bs-toggle="tab"
            href="#general" role="tab" aria-controls="general" aria-selected="true" data-original-title=""
            title="" data-tab="0">
            <i data-feather="settings"></i>{{ __('static.general') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="faq-tab" data-bs-toggle="tab" href="#faq" role="tab" aria-controls="faq"
            aria-selected="true" data-original-title="" title="" data-tab="2">
            <i data-feather="help-circle"></i> {{ __('FAQ\'s') }}</a>
    </li>
</ul>
<div class="tab-content" id="servicetabContent">
    <div class="tab-pane fade {{ session('active_tab') != null ? '' : 'show active' }}" id="general" role="tabpanel" aria-labelledby="general-tab">
        <div class="form-group row">
            <label class="col-md-2" for="title">{{ __('static.title') }}<span> *</span></label>
            <div class="col-md-10">
                <input class='form-control' type="text" id="title" name="title"
                    value="{{ isset($providerSiteService->title) ? $providerSiteService->title : old('title') }}"
                    placeholder="{{ __('static.service.enter_title') }}">
                @error('title')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="category_id">{{ __('static.service.category') }}<span> *</span></label>
            <div class="col-md-10 error-div select-dropdown">
                <select id="category_id" class="select-2 form-control categories"
                    data-placeholder="{{ __('static.service.select_categories') }}" search="true" name="category_id[]"
                    multiple>
                    <option value=""></option>
                    @foreach ($categories as $key => $value)
                        <option value="{{ $key }}"
                            @if (isset($default_categories) && in_array($key, $default_categories)) selected 
                            @elseif (old('category_id') && in_array($key, old('category_id'))) selected @endif>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <input type="hidden" name="service_type" value="{{ ServiceTypeEnum::PROVIDER_SITE }}">
        @hasrole('admin')
            <div class="form-group row">
                <label class="col-md-2" for="provider_id">{{ __('static.service.provider') }}<span> *</span></label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control" id="provider_id" name="provider_id"
                        data-placeholder="{{ __('static.service.select_provider') }}">
                        <option class="select-placeholder" value=""></option>
                        @foreach ($providers as $key => $option)
                            <option class="option" value="{{ $key }}"
                                @if (old('provider_id', isset($providerSiteService) ? $providerSiteService->user_id : '') == $key) selected @endif>{{ $option }}</option>
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
            <input type="hidden" name="provider_id" value="{{ auth()->user()->id }}" id="provider_id">
        @endhasrole
        <div class="form-group row">
            <label class="col-md-2" for="price">{{ __('static.service.price') }}<span> *</span></label>
            <div class="col-md-10 error-div">
                <div class="input-group mb-3 flex-nowrap">
                    <span class="input-group-text">{{ Helpers::getSettings()['general']['default_currency']->symbol }}</span>
                    <div class="w-100">
                        <input class='form-control' type="number" id="price" name="price" min="1"
                            value="{{ isset($providerSiteService->price) ? $providerSiteService->price : old('price') }}"
                            placeholder="{{ __('static.coupon.price') }}">
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
            <label class="col-md-2" for="discount">{{ __('static.service.discount') }}</label>
            <div class="col-md-10 error-div">
                <div class="input-group mb-3 flex-nowrap">
                    <div class="w-100 percent">
                        <input class='form-control' id="discount" type="number" name="discount" min="1"
                            value="{{ $providerSiteService->discount ?? old('discount') }}"
                            placeholder="{{ __('static.service.enter_discount') }}"
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
            <label class="col-md-2" for="service_rate">{{ __('static.service.service_rate') }}</label>
            <div class="col-md-10">
                <input class='form-control' type="number" id="service_rate" name="service_rate"
                    value="{{ isset($providerSiteService->service_rate) ? $providerSiteService->service_rate : old('service_rate') }}"
                    placeholder="{{ __('static.service.service_rate') }}" readonly>
                @error('service_rate')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="duration">{{ __('static.service.duration') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="number" min="1" name="duration" id="duration"
                    value="{{ isset($providerSiteService->duration) ? $providerSiteService->duration : old('duration') }}"
                    placeholder="{{ __('static.service.enter_duration') }}">
                @error('duration')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="duration_unit">{{ __('static.service.duration_unit') }}<span> *</span></label>
            <div class="col-md-10 error-div select-dropdown">
                <select class="select-2 form-control" id="duration_unit" name="duration_unit"
                    data-placeholder="{{ __('static.service.select_duration_unit') }}">
                    <option class="select-placeholder" value=""></option>
                    @foreach (['hours' => 'Hours', 'minutes' => 'Minutes'] as $key => $option)
                        <option class="option" value="{{ $key }}" @if (old('duration_unit', $providerSiteService->duration_unit ?? '') === $key) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
                @error('duration_unit')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="thumbnail" class="col-md-2">{{ __('static.categories.thumbnail') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="file" id="thumbnail" accept=".jpg, .png, .jpeg"
                    name="thumbnail">
                @error('thumbnail')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        @if (isset($providerSiteService) && isset($providerSiteService->getFirstMedia('thumbnail')->original_url))
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-10">
                        <div class="image-list">
                            <div class="image-list-detail">
                                <div class="position-relative">
                                    <img src="{{ $providerSiteService->getFirstMedia('thumbnail')->original_url }}"
                                        id="{{ $providerSiteService->getFirstMedia('thumbnail')->id }}" alt="User Image"
                                        class="image-list-item">
                                    <div class="close-icon">
                                        <i data-feather="x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="form-group row">
            <label for="image" class="col-md-2">{{ __('static.categories.image') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="file" id="image" accept=".jpg, .png, .jpeg" name="image[]"
                    multiple>
                @error('image')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        @if (isset($providerSiteService->media) && !$providerSiteService->media->isEmpty())
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-10">
                        <div class="image-list">
                            @foreach ($providerSiteService->getMedia('image') as $media)
                                <div class="image-list-detail">
                                    <div class="position-relative">
                                        <img src="{{ $media->original_url }}" id="{{ $media->id }}"
                                            alt="User Image" class="image-list-item">
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
        <div class="form-group row">
            <label for="description" class="col-md-2">{{ __('static.service.description') }}</label>
            <div class="col-md-10">
                <textarea class="form-control" rows="4" name="description"
                    placeholder="{{ __('static.service.enter_description') }}" cols="50">{{ $providerSiteService->description ?? old('description') }}</textarea>
                @error('description')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="role">{{ __('static.service.is_random_related_services') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($providerSiteService))
                            <input class="form-control" type="hidden" name="is_random_related_services"
                                value="0">
                            <input class="form-check-input" id="is_related" type="checkbox"
                                name="is_random_related_services" id="" value="1"
                                {{ $providerSiteService->is_random_related_services ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="is_random_related_services"
                                value="0">
                            <input class="form-check-input" id="is_related" type="checkbox"
                                name="is_random_related_services" id="" value="1">
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row services" @if (isset($providerSiteService) && $providerSiteService->is_random_related_services) style="display:none" @endif>
            <label class="col-md-2" for="service_id">{{ __('static.service.related_services') }} <span>
                    *</span></label>
            <div class="col-md-10 error-div select-dropdown">
                <select id="related_services" class="select-2 form-control" search="true" name="service_id[]"
                    data-placeholder="{{ __('static.service.select_related_services') }}" multiple>
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
            <label class="col-md-2" for="status">{{ __('static.status') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($providerSiteService))
                            <input class="form-control" type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1"
                                {{ $providerSiteService->status ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="footer">
            <button type="button" class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
        </div>
    </div>
</div>
<div class="tab-pane fade" id="faq" role="tabpanel" aria-labelledby="faq-tab">
    <div class="faq-container mb-2">
        @if (isset($providerSiteService) && !$providerSiteService->faqs->isEmpty())
            @foreach ($providerSiteService->faqs as $index => $faq)
                <div class="faqs-structure mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-11 col-sm-10 col-12">
                            <div class="form-group row">
                                <label class="col-md-2"
                                    for="faqs[{{ $index }}][question]">{{ __('static.service.question') }}</label>
                                <div class="col-md-10">
                                    <input class="form-control" type="text"
                                        name="faqs[{{ $index }}][question]"
                                        id="faqs[{{ $index }}][question]"
                                        placeholder="{{ __('static.service.enter_question') }}"
                                        value="{{ $faq['question'] }}" required>
                                </div>
                            </div>
                            <input type="hidden" name="faqs[{{ $index }}][id]" value="{{ $faq['id'] }}">
                            <div class="form-group row">
                                <label class="col-md-2"
                                    for="faqs[{{ $index }}][answer]">{{ __('static.service.answer') }}</label>
                                <div class="col-md-10">
                                    <textarea class="form-control" name="faqs[{{ $index }}][answer]" id="faqs[{{ $index }}][answer]"
                                        placeholder="{{ __('static.service.enter_answer') }}" cols="30" rows="5">{{ $faq['answer'] }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1 col-sm-2 col-12">
                            <div class="form-group row">
                                <label
                                    class="col-12 opacity-0 d-sm-flex d-none">{{ __('static.service.action') }}</label>
                                <div class="col-12">
                                    <div class="remove-faq mt-4">
                                        <i data-feather="trash-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="faqs-structure mb-4">
                <div class="row align-items-center">
                    <div class="col-md-11 col-sm-10 col-12">
                        <div class="form-group row">
                            <label class="col-md-2"
                                for="faqs[0][question]">{{ __('static.service.question') }}</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" name="faqs[0][question]"
                                    id="faqs[0][question]" value="{{ old('faqs[0][question]') }}"
                                    placeholder="{{ __('static.service.enter_question') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2" for="faqs[0][answer]">{{ __('static.service.answer') }}</label>
                            <div class="col-md-10">
                                <textarea class="form-control" name="faqs[0][answer]" id="faqs[0][answer]"
                                    placeholder="{{ __('static.service.enter_answer') }}" cols="30" rows="5">{{ old('faqs[0][answer]') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-sm-2 col-12">
                        <div class="form-group row">
                            <label
                                class="col-12 opacity-0 d-sm-flex d-none">{{ __('static.service.action') }}</label>
                            <div class="col-12">
                                <div class="remove-faq mt-4">
                                    <i data-feather="trash-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="col-md-11 col-10">
        <div class="form-group row mt-4">
            <label class="col-md-2"></label>
            <div class="col-md-10">
                <button type="button" id="add-faq"
                    class="btn btn-secondary add-faq">{{ __('static.service.add_faq') }}</button>
            </div>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="previousBtn btn cancel">{{ __('static.previous') }}</button>
        <button class="btn btn-primary submitBtn spinner-btn" type="submit">{{ __('static.submit') }}</button>
    </div>
</div>

@push('js')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#serviceForm").validate({
                    ignore: [],
                    rules: {
                        "title": "required",
                        "category_id[]": "required",
                        "service_type": "required",
                        "provider_id": {
                            required: isProvider
                        },
                        "price": "required",
                        "tax_id": "required",
                        "duration": "required",
                        "duration_unit": "required",
                        "image[]": {
                            required: isServiceImage,
                            accept: "image/jpeg, image/png"
                        },
                        "thumbnail": {
                            required: isServiceImage,
                            accept: "image/jpeg, image/png"
                        },
                        "service_id[]": {
                            required: isServiceRelated
                        },
                    },
                    messages: {
                        "image[]": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });

                $('#add-faq').unbind().click(function() {
                    var allInputsFilled = true;

                    $('.faqs-structure').find('.form-group.row').each(function() {
                        var question = $(this).find('input[name^="faqs"]').val()?.trim();
                        var answer = $(this).find('input[name^="faqs"]').val()?.trim();

                        if (question === '' || answer === '') {
                            allInputsFilled = false;
                            $(this).find('input[name^="faqs"]').addClass('is-invalid');
                            $(this).find('input[name^="faqs"]').removeClass('is-valid');
                        } else {
                            $(this).find('input[name^="faqs"]').removeClass('is-invalid');
                        }

                    });


                    if (!allInputsFilled) {
                        return;
                    }

                    var inputGroup = $('.faqs-structure').last().clone();
                    var newIndex = $('.faqs-structure').length;

                    inputGroup.find('input[name^="faqs"]').each(function() {
                        var oldName = $(this).attr('name');
                        var newName = oldName.replace(/\[\d+\]/, '[' + newIndex + ']');
                        $(this).attr('name', newName).val('');
                    });

                    inputGroup.find('textarea[name^="faqs"]').each(function() {
                        var oldName = $(this).attr('name');
                        var newName = oldName.replace(/\[\d+\]/, '[' + newIndex + ']');
                        $(this).attr('name', newName).val('');
                    });

                    $(".faq-container").append(inputGroup);

                });

                $(document).on('click', '.remove-faq', function() {
                    $(this).closest('.faqs-structure').remove();
                });

                $('#price, #discount').on('input', updateServiceRate);
                updateServiceRate();

                $(document).on('change', '#is_related', function(e) {
                    let status = $(this).prop('checked') == true ? 1 : 0;
                    if (status == true) {
                        $('.services').hide();
                    } else {
                        $('.services').show();
                    }
                });
                var initialProviderID = $('input[name="provider_id"]').val() || $('select[name="provider_id"]')
                    .val();
                if (initialProviderID) {
                    loadServices(initialProviderID);
                }
                @isset($providerSiteService)
                    var selectedServices = {!! json_encode(
                        $providerSiteService->related_services->where('id', '!=', $providerSiteService->id)->pluck('id')->toArray() ?? [],
                    ) !!};
                    loadServices(initialProviderID, selectedServices);
                @endisset

                $('select[name="provider_id"]').on('change', function() {
                    var providerID = $(this).val();
                    loadServices(providerID);
                });
            });
        })(jQuery);

        function loadServices(providerID, selectedServices) {

            let url = "{{ route('backend.get-provider-services', '') }}";
            if (providerID) {
                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        provider_id: providerID,
                        service_id: "{{ $providerSiteService->id ?? null }}"
                    },
                    success: function(data) {
                        $('#related_services').empty();
                        $.each(data, function(id, text) {
                            var option = new Option(text, id);

                            if (selectedServices && selectedServices.includes(String(id))) {
                                $('#related_services').append(option);
                            } else {
                                $('#related_services').append(option);
                            }
                        });

                        $('#related_services').val(selectedServices).trigger('change');
                    },
                });
            }
        }

        function updateServiceRate() {
            var price = parseFloat($('#price').val()) || 0;
            var discount = parseFloat($('#discount').val()) || 0;
            var serviceRate = price - (price * (discount / 100));
            $('#service_rate').val(serviceRate.toFixed(2));
        }

        function isServiceImage() {
            @if (isset($providerSiteService->media) && !$providerSiteService->media->isEmpty())
                return false;
            @else
                return true;
            @endif
        }

        function isProvider() {
            @if (auth()->user()->hasrole('provider'))
                return false;
            @else
                return true;
            @endif
        }

        function isServiceRelated() {
            return $('#is_related').prop('checked') ? false : true;
        }
    </script>
@endpush
