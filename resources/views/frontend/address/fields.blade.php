@use('app\Helpers\Helpers')
@use('app\Models\State')
@php
    $countries = Helpers::getCountries();
    $countryCodes = Helpers::getCountryCodes();
    $states = [];
    if (isset($address->country_id) || old('country_id')) {
        $states = State::where('country_id', old('country_id', @$address->country_id))?->get();
    }
@endphp
<div class="row g-3">
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="role">{{ __('static.address_category') }}</label>
            <div class="form-group category-list">
                <div class="form-check form-radio">
                    <input type="radio" name="address_type" id="home" value="Home" class="form-check-input"
                        @isset($address->type){{ $address->type == 'Home' ? 'checked' : '' }}@endisset
                        checked>
                    <label class="form-check-label mb-0 cursor-pointer" for="home">
                        {{ __('static.home') }}
                        <span class="check-box"></span>
                    </label>
                </div>
                <div class="form-check form-radio">
                    <input type="radio" name="address_type" id="work" value="Work" class="form-check-input"
                        @isset($address->type){{ $address->type == 'Work' ? 'checked' : '' }}@endisset>
                    <label class="form-check-label mb-0 cursor-pointer" for="work">{{ __('static.work') }} <span class="check-box"></span></label>
                </div>
                <div class="form-check form-radio">
                    <input type="radio" name="address_type" id="other" value="Other" class="form-check-input"
                        @isset($address->type){{ $address->type == 'Other' ? 'checked' : '' }}@endisset>
                    <label class="form-check-label mb-0 cursor-pointer" for="other">{{ __('static.other') }} <span class="check-box"></span></label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="alternative_name">{{ __('static.address.alternative_name') }}</label>
            <div class="w-100">
                <input class='form-control' type="text" name="alternative_name" id="alternative_name"
                    value="{{ $address->alternative_name ?? old('alternative_name') }}"
                    placeholder="{{ __('static.address.enter_alternative_name') }}">
                @error('alternative_name')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="alternative_phone">{{ __('static.address.alternative_phone') }}</label>
            <div class="w-100">
                <div class="input-group phone-detail">
                    <select class="select-2 form-control select-country-code" name="code" data-placeholder="">
                        @php
                            $default = old('alternative_code', $address->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                        @endphp
                        <option value="" selected></option>
                        @foreach (Helpers::getCountryCodes() as $key => $option)
                            <option class="option" value="{{ $option->phone_code }}"
                                data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                @if ($option->phone_code == $default) selected @endif
                                data-default="old('alternative_code')">
                                +{{ $option->phone_code }}
                            </option>
                        @endforeach
                    </select>
                    @error('alternative_code')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <input class="form-control" type="number" name="alternative_phone" id="alternative_phone"
                        value="{{ $address->alternative_phone ?? old('alternative_phone') }}" min="1"
                        placeholder="{{ __('static.address.enter_alternative_phone') }}">
                </div>
            </div>
        </div>
    </div>


    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="address">{{ __('static.users.address') }} <span
                    class="required-span">*</span></label>
            <div class="w-100">
                <textarea class="form-control ui-widget autocomplete-google" placeholder="Enter Address " rows="4" id="address"
                    name="address" cols="50">{{ $address->address ?? old('address') }}</textarea>
                @error('address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="category-list-box">
            <label class="label-title" for="country">{{ __('static.users.country') }} <span
                    class="required-span">*</span></label>
            <div class="w-100 error-div select-dropdown border-0 p-0 m-0">
                <select class="select-2 form-control select-country" id="country_id" name="country_id"
                    data-placeholder="{{ __('static.users.select_country') }}">
                    <option class="select-placeholder" value=""></option>
                    @php
                        $default = old('country_id', @$address->country_id);
                    @endphp
                    @foreach ($countries as $key => $option)
                        <option class="option" value={{ $key }}
                            @if ($key == $default) selected @endif data-default="{{ $default }}">
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
                @error('country_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="category-list-box">
            <label class="label-title" for="state">{{ __('static.users.state') }} <span
                    class="required-span">*</span></label>
            <div class="w-100 error-div select-dropdown border-0 p-0 m-0">
                <select class="select-2 form-control select-state"
                    data-placeholder="{{ __('static.users.select_state') }}" id="state_id" name="state_id"
                    data-default-state-id="{{ old('state_id') }}">
                    <option class="select-placeholder" value=""></option>
                    @php
                        $default = old('state_id', @$address->state_id);
                    @endphp
                    @if (count($states))
                        @foreach ($states as $key => $state)
                            <option class="option" value={{ $state->id }}
                                @if ($state->id == $default) selected @endif data-default="{{ $default }}">
                                {{ $state->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('state_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="branch_name">{{ __('static.city') }} <span
                    class="required-span">*</span></label>
            <div class="w-100">
                <input class="form-control" id="city" type="text" name="city"
                    value="{{ isset($address->city) ? $address->city : old('city') }}"
                    placeholder="{{ __('static.users.enter_city') }}">
                @error('city')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="area">{{ __('static.provider.street_address') }} <span
                    class="required-span">*</span></label>
            <div class="w-100">
                <input class="form-control" type="text" id="area" name="street_address"
                    value="{{ isset($address->street_address) ? $address->street_address : old('street_address') }}"
                    placeholder="{{ __('static.provider.enter_street_address') }}">
                @error('street_address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="category-list-box">
            <label class="label-title" for="postal_code">{{ __('static.postal_code') }} <span
                    class="required-span">*</span></label>
            <div class="w-100">
                <input class="form-control" type="text" id="postal_code" name="postal_code"
                    value="{{ isset($address->postal_code) ? $address->postal_code : old('postal_code') }}"
                    placeholder="{{ __('static.users.postal_code') }}">
                @error('postal_code')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    @if(isset($address) && !$address->is_primary)
        <div class="col-12">
            <div class="set-address-box">
                <label for="role">{{ __('static.address.set_as_is_primary') }}</label>
                <input class="form-check-input" type="checkbox" name="is_primary" value="1">
            </div>
        </div>
    @elseif (!isset($address))
        <div class="col-12">
            <div class="set-address-box">
                <label for="role">{{ __('static.address.set_as_is_primary') }}</label>
                <input class="form-check-input" type="checkbox" name="is_primary" value="1">
            </div>
        </div>
    @endif

</div>


@push('js')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_map_api_key') }}&libraries=places"></script>

<script>
    window.gm_authFailure = function() {
        toastr.error(
            "Google Maps authentication failed. Please check your API key or ensure the Maps JavaScript API is enabled."
        );
    };

    window.addEventListener("error", function(e) {
        if (e.message && e.message.toLowerCase().includes("google maps")) {
            toastr.error("Google Maps failed to load. Check if the Maps JavaScript API is enabled.");
        }
    });

    (function($) {
        "use strict";

        $(document).ready(function() {
            
            // ========== THIS CODE TO FIX SELECT2 SEARCH INPUT FIELD NOT CLICKABLE ==========

                // Fix for Select2 search input not clickable in modals
                $(document).on('select2:open', function(e) {
                    const dropdown = $('.select2-container--open');
                    dropdown.css('z-index', 9999);
                });

                $('.select-2').each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : document.body
                    });
                });

                $('.modal').on('shown.bs.modal', function() {
                    $(this).find('.select-2').select2({
                        dropdownParent: $(this)
                    });
                });

            // ========== END OF SELECT2 ISSUE ==========

            // When any address modal is opened
            $(document).on('shown.bs.modal', '.address-modal', function () {
                const $modal = $(this);

                // Initialize Google Autocomplete
                $modal.find(".autocomplete-google").each(function () {
                    const input = this;

                    if (!input._autocompleteInitialized) {
                        const autocomplete = new google.maps.places.Autocomplete(input);

                        autocomplete.addListener("place_changed", function () {
                            const place = autocomplete.getPlace();
                            if (!place.place_id) return;

                            getAddressDetails(place.place_id, $modal);
                        });

                        input._autocompleteInitialized = true;
                    }
                });

                // Trigger state population if country is already selected
                $modal.find('.select-country').on('change', function() {
                    const countryId = $(this).val();
                    populateStates(countryId, null, $modal);
                });
            });

            // Get address details using Google Place ID
            function getAddressDetails(placeId, $modal) {
                $.ajax({
                    url: "/backend/google-address",
                    type: 'GET',
                    dataType: "json",
                    data: {
                        placeId: placeId,
                    },
                    success: function(data) {
                        $modal.find('#latitude').val(data.location.lat);
                        $modal.find('#longitude').val(data.location.lng);
                        $modal.find('#lat').val(data.location.lat);
                        $modal.find('#lng').val(data.location.lng);

                        $modal.find('#city').val(data.locality);
                        $modal.find('#postal_code').val(data.postal_code);

                        let street = '';
                        if (data.streetNumber) street += data.streetNumber + ", ";
                        if (data.streetName) street += data.streetName + ", ";
                        $modal.find('#street_address_1').val(street);
                        $modal.find('#area').val(data.area);

                        if (data.country_id) {
                            $modal.find('#country_id').val(data.country_id).trigger('change');
                        }

                        if (data.state_id) {
                            $modal.find('.select-state').val(data.state_id).trigger('change');
                            populateStates(data.country_id, data.state_id, $modal);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("AJAX error in getAddressDetails:", textStatus, errorThrown);
                    }
                });
            }

            // Populate states for selected country
            function populateStates(countryId, selectedStateId = null, $modal) {
                const $stateSelect = $modal.find('.select-state');
                $stateSelect.html('<option value="">{{ __("static.users.select_state") }}</option>');

                if (!countryId) return;

                $.ajax({
                    url: "{{ url('/states') }}",
                    type: "POST",
                    data: {
                        country_id: countryId,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(result) {
                        $.each(result.states, function(key, value) {
                            $stateSelect.append(
                                `<option value="${value.id}" ${value.id == selectedStateId ? 'selected' : ''}>${value.name}</option>`
                            );
                        });

                        // Apply default if needed
                        const defaultStateId = $stateSelect.data("data-default-state-id");
                        if (defaultStateId) {
                            $stateSelect.val(defaultStateId).trigger('change');
                        }
                    },
                    error: function(err) {
                        console.error("Failed to populate states", err);
                    }
                });
            }

            $("#addressForm,#editAddressForm").each(function() {
                $(this).validate({
                    ignore: [],
                    rules: {
                        "country_id": "required",
                        "state_id": "required",
                        "city": "required",
                        "street_address": "required",
                        "postal_code": "required",
                        "address": "required"
                    }
                });
            });
        });

    })(jQuery);
</script>
@endpush