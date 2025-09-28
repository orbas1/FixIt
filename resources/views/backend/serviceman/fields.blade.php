<ul class="nav nav-tabs nav-material" id="servicemanTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active show" id="general-tab" data-bs-toggle="tab" href="#general" role="tab"
            aria-controls="general" aria-selected="true" data-tab="0">
            <i data-feather="settings"></i>{{ __('static.serviceman.general') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address" role="tab" aria-controls="address"
            aria-selected="false" data-tab="1">
            <i data-feather="map-pin"></i>{{ __('static.provider.address') }}
        </a>
    </li>
</ul>
<div class="tab-content" id="servicemanTabContent">
    <div class="tab-pane fade active show" id="general" role="tabpanel" aria-labelledby="general-tab">
        <div class="form-group row">
            <label for="image" class="col-md-2">{{ __('static.serviceman.image') }}</label>
            <div class="col-md-10">
                <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="image" name="image">
                @error('image')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        @if (isset($serviceman) && isset($serviceman->getFirstMedia('image')->original_url))
            <div class="form-group">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-10">
                        <div class="image-list">
                            <div class="image-list-detail">
                                <div class="position-relative">
                                    <img src="{{ $serviceman->getFirstMedia('image')->original_url }}"
                                        id="{{ $serviceman->getFirstMedia('image')->id }}" alt="User Image"
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
            <label class="col-md-2" for="name">{{ __('static.name') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="text" id="name" name="name"
                    value="{{ isset($serviceman->name) ? $serviceman->name : old('name') }}"
                    placeholder="{{ __('static.serviceman.enter_name') }}">
                @error('name')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="email">{{ __('static.email') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="text" name="email" id="email"
                    value="{{ isset($serviceman->email) ? $serviceman->email : old('email') }}"
                    placeholder="{{ __('static.serviceman.enter_email') }}">
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="phone">{{ __('static.phone') }}<span> *</span></label>
            <div class="col-md-10">
                <div class="input-group mb-3 phone-detail">
                    <div class="col-sm-1">
                        <select class="select-2 form-control select-country-code" id="select-country-code"
                            name="code" data-placeholder="">
                            @php
                                $default = old('code', $serviceman->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                            @endphp
                            <option value="" selected></option>
                            @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                                <option class="option" value="{{ $option->phone_code }}"
                                    data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                    @if ($option->phone_code == $default) selected @endif
                                    data-default="{{ $default }}">+{{ $option->phone_code }}</option>
                            @endforeach
                        </select>
                        @error('code')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-sm-11">
                        <input class="form-control" type="number" name="phone" id="phone"
                            value="{{ isset($serviceman->phone) ? $serviceman->phone : old('phone') }}" min="1"
                            placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                    </div>
                </div>
                @error('phone')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        @hasrole('admin')
            <div class="form-group row">
                <label class="col-md-2" for="provider_id">{{ __('static.serviceman.provider') }}<span> *</span></label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control user-dropdown" id="provider_id" name="provider_id"
                        data-placeholder="{{ __('static.serviceman.select-provider') }}">
                        <option class="select-placeholder" value=""></option>
                        @foreach ($providers->where('type', 'company') as $key => $provider)
                            <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                                image="{{ $provider->getFirstMedia('image')?->getUrl() }}"
                                @if (old('provider_id', isset($serviceman) ? $serviceman->provider->id : '') == $provider->id) selected @endif>
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
        <div class="form-group row">
            <label class="col-md-2" for="experience_interval">{{ __('static.provider.experience_interval') }}<span>
                    *</span></label>
            <div class="col-md-10 error-div select-dropdown">
                <select class="select-2 form-control" id="experience_interval" name="experience_interval"
                    data-placeholder="{{ __('static.provider.select_experience_interval') }}">
                    <option class="select-placeholder" value=""></option>
                    @foreach (['years' => 'Years', 'months' => 'Months'] as $key => $option)
                        <option class="option" value="{{ $key }}"
                            @if (old('experience_interval', isset($serviceman) ? $serviceman->experience_interval : '') == $key) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
                @error('experience_interval')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="experience_duration">{{ __('static.provider.experience_duration') }}<span>
                    *</span></label>
            <div class="col-md-10">
                <input class="form-control" id="experience_duration" type="number" min="1"
                    name="experience_duration"
                    value="{{ $serviceman->experience_duration ?? old('experience_duration') }}"
                    placeholder="{{ __('static.provider.enter_experience_duration') }}">
                @error('experience_duration')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class ='form-group row'>
            <label class="col-md-2" for="known_languages">{{ __('static.users.known_languages') }}</label>
            <div class="col-md-10 error-div select-dropdown">
                <select id="serviceman" class="select-2 form-control language" search="true"
                    name="known_languages[]" multiple="multiple"
                    data-placeholder="{{ __('static.users.select_languages') }}">
                    @foreach ($languages as $key => $value)
                        <option
                            value="{{ $key }}"@if (old('known_languages') && in_array($key, old('known_languages'))) selected="selected" @elseif (isset($userKnownLanguages) && in_array($key, $userKnownLanguages)) selected="selected" @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                @error('known_languages')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        @if (Request::is('backend/serviceman/create'))
            <div class="form-group row">
                <label class="col-md-2" for="password">{{ __('static.password') }}<span> *</span></label>
                <div class="col-md-10">
                    <div class="position-relative">
                        <input class="form-control" id="password" type="password" name="password"
                            value="{{ old('password') }}"
                            placeholder="{{ __('static.serviceman.enter_password') }}">
                        <div class="toggle-password">
                            <i data-feather="eye" class="eye"></i>
                            <i data-feather="eye-off" class="eye-off"></i>
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2" for="confirm_password">{{ __('static.confirm_password') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <div class="position-relative">
                        <input class="form-control" id="confirm_password" type="password" name="confirm_password"
                            placeholder="{{ __('static.serviceman.re_enter_password') }}">
                        <div class="toggle-password">
                            <i data-feather="eye" class="eye"></i>
                            <i data-feather="eye-off" class="eye-off"></i>
                        </div>
                        @error('confirm_password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class = "form-group row">
                <label for="address" class="col-md-2">{{ __('static.service.description') }}</label>
                <div class="col-md-10">
                    <textarea class="form-control" name="description" id="description"
                        placeholder="{{ __('static.service.enter_description') }}" rows="4" cols="50">{{ $serviceman->description ?? old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        @endif
        {{-- <div class="form-group row">
            <label class="col-md-2" for="role">{{ __('static.serviceman.is_featured') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($serviceman))
                            <input class="form-control" type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                {{ $serviceman->is_featured ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div> --}}
        <div class="form-group row">
            <label class="col-md-2" for="role">{{ __('static.serviceman.status') }}</label>
            <div class="col-md-10">
                <div class="editor-space">
                    <label class="switch">
                        @if (isset($serviceman))
                            <input class="form-control" type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1"
                                {{ $serviceman->status ? 'checked' : '' }}>
                        @else
                            <input class="form-control" type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                        @endif
                        <span class="switch-state"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
        </div>
    </div>
    <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
        <div class="form-group row">
            <label class="col-md-2" for="role">{{ __('static.address_category') }}</label>
            <div class="col-md-10">
                <div class="form-group row d-flex align-items-center gap-sm-4 gap-3 ms-0">
                    <div class="form-check w-auto form-radio">
                        <input type="radio" name="address_type" id="home" value="home"
                            class="form-check-input me-2"
                            @isset($serviceman){{ $serviceman?->primaryAddress?->type == 'home' ? 'checked' : '' }}@endisset
                            checked>
                        <label class="form-check-label mb-0 cursor-pointer"
                            for="home">{{ __('static.home') }}</label>
                    </div>
                    <div class="form-check w-auto form-radio">
                        <input type="radio" name="address_type" id="work" value="work"
                            class="form-check-input me-2"
                            @isset($serviceman){{ $serviceman?->primaryAddress?->type == 'work' ? 'checked' : '' }}@endisset>
                        <label class="form-check-label mb-0 cursor-pointer"
                            for="work">{{ __('static.work') }}</label>
                    </div>
                    <div class="form-check w-auto form-radio">
                        <input type="radio" name="address_type" id="other" value="other"
                            class="form-check-input me-2"
                            @isset($serviceman){{ $serviceman?->primaryAddress?->type == 'other' ? 'checked' : '' }}@endisset>
                        <label class="form-check-label mb-0 cursor-pointer"
                            for="other">{{ __('static.other') }}</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="alternative_name">{{ __('static.address.alternative_name') }}</label>
            <div class="col-md-10">
                <input class='form-control' type="text" name="alternative_name" id="alternative_name"
                    value="{{ $serviceman?->primaryAddress?->alternative_name ?? old('alternative_name') }}"
                    placeholder="{{ __('static.address.enter_alternative_name') }}">
                @error('alternative_name')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2" for="phone">{{ __('static.address.alternative_phone') }}</label>
            <div class="col-md-10">
                <div class="input-group mb-3 phone-detail">
                    <div class="col-sm-1">
                        <select class="select-2 form-control select-country-code" name="alternative_code"
                            data-placeholder="">
                            @php
                                $default = old('alternative_code', $serviceman?->primaryAddress?->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                            @endphp
                            <option value="" selected></option>
                            @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
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
                    </div>
                    <div class="col-sm-11">
                        <input class="form-control" type="number" name="alternative_phone" id="alternative_phone"
                            value="{{ $serviceman?->primaryAddress?->alternative_phone ?? old('alternative_phone') }}"
                            min="1" placeholder="{{ __('static.address.enter_alternative_phone') }}">
                    </div>
                </div>
                @error('phone')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class = "form-group row">
            <label for="address" class="col-md-2">{{ __('static.users.address') }}</label>
            <div class="col-md-10">
                <textarea class = "form-control autocomplete-google" id="address" placeholder="{{ __('static.users.enter_address') }}"
                    rows="4" name="address" cols="50">{{ $serviceman?->primaryAddress?->address ?? old('address') }}</textarea>
                @error('address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="country" class="col-md-2">{{ __('static.users.country') }}<span> *</span></label>
            <div class="col-md-10 error-div select-dropdown">
                <select class="select-2 form-control select-country" id="country_id" name="country_id"
                    data-placeholder="{{ __('static.users.select_country') }}">
                    <option class="select-placeholder" value=""></option>
                    @forelse ($countries as $key => $option)
                        <option class="option" value={{ $key }}
                            @if (old('country_id', isset($serviceman) ? $serviceman?->primaryAddress?->country_id : '') == $key) selected @endif>{{ $option }}</option>
                    @empty
                        <option value="" disabled></option>
                    @endforelse
                </select>
                @error('country_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        @php
            if (isset($serviceman?->primaryAddress?->country_id) || old('country_id')) {
                $states = \App\Models\State::where(
                    'country_id',
                    old('country_id', @$serviceman?->primaryAddress?->country_id),
                )->get();
            } else {
                $states = [];
            }
        @endphp
        <div class="form-group row">
            <label for="country" class="col-md-2">{{ __('static.users.state') }}<span> *</span></label>
            <div class="col-md-10 error-div select-dropdown">
                @php
                    $default = old('state_id', @$serviceman?->primaryAddress?->state_id);
                @endphp
                <select class="select-2 form-control select-state"
                    data-default-state-id="{{ old('state_id') }}"
                    data-placeholder="{{ __('static.users.select_state') }}" id="state_id" name="state_id">
                    <option value=""></option>
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
        <div class="form-group row">
            <label class="col-md-2" for="city">{{ __('static.city') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="text" name="city" id="city"
                    value="{{ $serviceman?->primaryAddress?->city ?? old('city') }}"
                    placeholder="{{ __('static.users.enter_city') }}">
                @error('city')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
            <div class="form-group row">
                <label for="street_address" class="col-md-2">{{ __('static.provider.street_address') }}</label>
                <div class="col-md-10">
                    <input type="text" class="form-control ui-widget" id="street_address_1"
                        name="street_address" placeholder="{{ __('static.provider.enter_street_address') }}"
                        value="{{ $serviceman?->primaryAddress?->street_address ?? old('street_address') }}">
                </div>
                @error('street_address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        <div class="form-group row">
            <label class="col-md-2" for="postal_code">{{ __('static.postal_code') }}<span> *</span></label>
            <div class="col-md-10">
                <input class="form-control" type="text" id="postal_code" name="postal_code"
                    value="{{ $serviceman?->primaryAddress?->postal_code ?? old('postal_code') }}"
                    placeholder="{{ __('static.users.postal_code') }}">
                @error('postal_code')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>


        <div class="card-footer">
            <button type="button" class="previousBtn btn cancel">{{ __('static.previous') }}</button>
            <button class="btn btn-primary submitBtn spinner-btn" type="submit">{{ __('static.submit') }}</button>
        </div>
    </div>
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
</script>

<script>
    (function($) {
        "use strict";

        $(document).ready(function() {

            function initializeGoogleAutocomplete() {

                try {
                    $(".autocomplete-google").each(function() {
                        var autocomplete = new google.maps.places.Autocomplete(this);

                        autocomplete.addListener("place_changed", function() {
                            var place = autocomplete.getPlace();
                            if (!place.place_id) {
                                console.log("No place details available");
                                return;
                            }

                            var placeId = place.place_id;
                            getAddressDetails(placeId);
                        });
                    });
                } catch (error) {
                    console.error("Google Maps Autocomplete error:", error);
                    toastr.error(
                        "Failed to initialize Google Maps Autocomplete. Please check your API configuration."
                        );
                }
            }

            function populateStates(countryId, state) {
                $(".select-state").html('');
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

                            console.log(key, value, "TEAXUDI")
                            $('.select-state').append(
                                `<option value="${value.id}" ${value.id === state ? 'selected' : ''}>${value.name}</option>`
                            );
                        });
                        console.log(result,defaultStateId)
                        var defaultStateId = $(".select-state").data("default-state-id");
                        if (defaultStateId !== '') {
                            $('.select-state').val(defaultStateId);
                        }
                    }
                });
            }

            function getAddressDetails(placeId) {

                $.ajax({
                    url: "/backend/google-address",
                    type: 'GET',
                    dataType: "json",
                    data: {
                        placeId: placeId,
                    },
                    success: function(data) {
                        console.log("address data", data.location)
                        $('#latitude').val(data.location.lat);
                        $('#longitude').val(data.location.lng);
                        $('#lat').val(data.location.lat);
                        $('#lng').val(data.location.lng);

                        $('#city').val(data.locality);
                        $('#postal_code').val(data.postal_code);
                        $('#postal_code').val(data.postal_code);
                        var street = '';
                        if (data.streetNumber) {
                            street += data.streetNumber + ", ";
                        }

                        if (data.streetName) {
                            street += data.streetName + ", ";
                        }
                        $('#street_address_1').val(street);
                        $('#area').val(data.area);
                        var countryId = data.country_id;
                        if (countryId) {
                            $('#country_id').val(countryId).trigger('change');
                        }

                        var stateId = data.state_id;
                        if (stateId) {
                            $('.select-state').val(stateId).trigger('change');
                            populateStates(countryId,stateId);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("AJAX error in getAddressDetails:", textStatus,
                            errorThrown);
                    }
                });
            }

            initializeGoogleAutocomplete();
            
        });

})(jQuery);
</script>

<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#servicemanForm").validate({
                ignore: [],
                rules: {
                    "image": {
                        accept: "image/jpeg, image/png"
                    },
                    "name": "required",
                    "email": {
                        required: true,
                        email: true
                    },
                    "phone": {
                        "required": true,
                        "minlength": 6,
                        "maxlength": 15
                    },
                    "provider_id": "required",
                    "experience_interval": "required",
                    "experience_duration": "required",
                    "password": {
                        required: isRequiredForEdit,
                        minlength: 8,
                    },
                    "confirm_password": {
                        required: isRequiredForEdit,
                        equalTo: "#password",
                        minlength: 8
                    },
                    "country_id": "required",
                    "state_id": "required",
                    "postal_code": "required",
                    "city": "required",
                    "area": "required",
                },
                messages: {
                    "image": {
                        accept: "Only JPEG and PNG files are allowed.",
                    },
                }
            });
        });

        function isRequiredForEdit() {
            return "{{ isset($serviceman) }}" ? false : true;
        }
    })(jQuery);
</script>
@endpush
