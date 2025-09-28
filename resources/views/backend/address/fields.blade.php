@php
    if (isset($address->country_id) || old('country_id')) {
        $states = \App\Models\State::where('country_id', old('country_id', @$address->country_id))->get();
    } else {
        $states = [];
    }
@endphp
@use('app\Helpers\Helpers')

<div class="form-group row">
    <label for="address" class="col-md-2">{{ __('static.users.address') }}<span> *</span></label>
    <div class="col-md-10">
        <textarea class="form-control ui-widget autocomplete-google" placeholder="Enter Address " rows="4" id="address"
            name="address" cols="50">{{ $address->address ?? old('address') }}</textarea>
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
            data-placeholder="{{ __('static.users.select_country') }}" required>
            <option class="select-placeholder" value=""></option>
            @php
                $default = old('country_id', @$address->country_id);
            @endphp
            @foreach ($countries as $key => $option)
                <option class="option" value={{ $key }} @if ($key == $default) selected @endif
                    data-default="{{ $default }}">
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
<div class="form-group row">
    <label for="state" class="col-md-2">{{ __('static.users.state') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control select-state" data-placeholder="{{ __('static.users.select_state') }}"
            id="state_id" name="state_id" data-default-state-id="{{ $address->state_id ?? '' }}" required>
            <option class="select-placeholder" value=""></option>
            @php
                $default = old('state_id', @$address->state_id);
            @endphp
            @if (count($states))
                @foreach ($states as $key => $state)
                    <option class="option" value={{ $state->id }} @if ($state->id == $default) selected @endif
                        data-default="{{ $default }}">
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
    <label class="col-md-2" for="branch_name">{{ __('static.city') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" id="city" type="text" name="city"
            value="{{ isset($address->city) ? $address->city : old('city') }}"
            placeholder="{{ __('static.users.enter_city') }}" required>
        @error('city')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>


<div class="form-group row">
    <label class="col-md-2" for="postal_code">{{ __('static.postal_code') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="text" id="postal_code" name="postal_code"
            value="{{ isset($address->postal_code) ? $address->postal_code : old('postal_code') }}"
            placeholder="{{ __('static.users.postal_code') }}" required>
        @error('postal_code')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if (in_array(request()->route()->getName(), ['serviceman', 'provider']))
<div class="form-group row">
    <label class="col-md-2" for="area">{{ __('static.area') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="text" id="area" name="area"
            value="{{ isset($address->area) ? $address->area : old('area') }}"
            placeholder="{{ __('static.users.enter_area') }}" required>
        @error('area')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@endif

@if (!in_array(request()->route()->getName(), ['serviceman', 'provider']))
<div class="form-group row">
    <label for="street_address" class="col-md-2">{{ __('static.provider.street_address') }}</label>
    <div class="col-md-10">
        <input type="text" class="form-control ui-widget" id="street_address_1"
            name="street_address" placeholder="{{ __('static.provider.enter_street_address') }}"
            value="{{ isset($address->street_address) ? $address->street_address : old('street_address') }}">
    </div>
    @error('street_address')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
@endif

@if (Helpers::isFirstAddress(@$address))
    <div class="form-group row">
        <label class="col-md-2" for="role">{{ __('static.address.is_primary') }}</label>
        <div class="col-md-10">
            <div class="editor-space">
                <label class="switch">
                    @if (isset($address))
                        <input class="form-control" type="hidden" name="is_primary" value="0">
                        <input class="form-check-input" type="checkbox" name="is_primary" value="1"
                            {{ $address->is_primary ? 'checked' : '' }}>
                    @else
                        <input class="form-control" type="hidden" name="is_primary" value="0">
                        <input class="form-check-input" type="checkbox" name="is_primary" value="1">
                    @endif
                    <span class="switch-state"></span>
                </label>
            </div>
        </div>
    </div>
@endif



