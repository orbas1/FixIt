    <ul class="nav nav-tabs" id="providerTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ session('active_tab') != null ? '' : 'show active' }}" id="provider-tab"
                data-bs-toggle="tab" href="#provider" role="tab" aria-controls="provider" aria-selected="true"
                data-tab="0">
                <i data-feather="settings"></i> {{ __('static.provider.provider_details') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="bank_details-tab" data-bs-toggle="tab" href="#bank_details" role="tab"
                aria-controls="address" aria-selected="true" data-tab="1">
                <i data-feather="briefcase"></i>
                {{ __('static.provider.bank_details') }}
            </a>
        </li>
        @if (!isset($provider) || (isset($provider) && $provider->type == 'company'))
            <li class="nav-item">
                <a class="nav-link" id="company-details-tab" data-bs-toggle="tab" href="#company_details" role="tab"
                    aria-controls="company_details" aria-selected="true" data-tab="2">
                    <i data-feather="book-open"></i>{{ __('static.provider.company_details') }}
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{ session('active_tab') == 'address_tab' ? 'show active' : '' }}" id="user_address-tab"
                data-bs-toggle="tab" href="#add_address" role="tab" aria-controls="address" aria-selected="true"
                data-tab="3">
                <i data-feather="map-pin"></i> {{ __('static.provider.address') }}
            </a>
        </li>
    </ul>
    <div class="tab-content" id="providerTabContent">
        <div class="tab-pane fade {{ session('active_tab') != null ? '' : 'show active' }}" id="provider"
            role="tabpanel" aria-labelledby="provider-tab">
            <div class="form-group row">
                <label class="col-md-2" for="type">{{ __('static.provider.type') }}<span> *</span></label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control" id="provider_type" name="type"
                        data-placeholder="{{ __('static.provider.select_type') }}"
                        @if (isset($provider)) disabled @endif>
                        <option value=""></option>
                        @foreach (['company' => 'Company', 'freelancer' => 'Freelancer'] as $key => $option)
                            <option class="option" value="{{ $key }}"
                                @if (old('type', isset($provider) ? $provider->type : '') == $key) selected @endif>{{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                @if (isset($provider))
                    <input type="hidden" name="type" value="{{ isset($provider) ? $provider->type : '' }}">
                @endif
            </div>
            <div class="form-group row">
                <label for="image" class="col-md-2">{{ __('static.provider.image') }}</label>
                <div class="col-md-10">
                    <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="provider-file-name"
                        name="image">
                    @error('image')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            @if (isset($provider) && isset($provider->getFirstMedia('image')->original_url))
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-10">
                            <div class="image-list">
                                <div class="image-list-detail">
                                    <div class="position-relative">
                                        <img src="{{ $provider->getFirstMedia('image')->original_url }}"
                                            id="{{ $provider->getFirstMedia('image')->id }}" alt="User Image"
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
                    <input class="form-control" type="text" name="name" id="name"
                        value="{{ isset($provider->name) ? $provider->name : old('name') }}"
                        placeholder="{{ __('static.users.enter_name') }}">
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
                    <input class="form-control" type="email" name="email" id="email"
                        value="{{ isset($provider->email) ? $provider->email : old('email') }}"
                        placeholder="{{ __('static.users.enter_email') }}">
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
                            <select class="select-2 form-control select-country-code" name="code"
                                data-placeholder="">
                                @php
                                    $default = old(
                                        'code',
                                        $provider->code ?? App\Helpers\Helpers::getDefaultCountryCode(),
                                    );
                                @endphp
                                <option value="" selected></option>
                                @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                                    <option class="option" value="{{ $option->phone_code }}"
                                        data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                        @if ($option->phone_code == $default) selected @endif
                                        data-default="{{ $default }}">
                                        +{{ $option->phone_code }}
                                    </option>
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
                                value="{{ isset($provider->phone) ? $provider->phone : old('phone') }}"
                                min="1" placeholder="{{ __('static.serviceman.enter_phone_number') }}"
                                maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                        </div>
                    </div>
                    @error('phone')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="zones">{{ __('static.zone.zones') }}<span> *</span> </label>
                <div class="col-md-10 error-div select-dropdown ">
                    <select id="blog_zones" class="select-2 form-control disable-all" id="zones[]" search="true"
                        name="zones[]" data-placeholder="{{ __('static.zone.select-zone') }}" multiple>
                        <option></option>
                        <option value="all">{{ __('static.report.select_all') }}</option>

                        @foreach ($zones as $key => $value)
                            <option value="{{ $key }}"
                                {{ (is_array(old('zones')) && in_array($key, old('zones'))) || (isset($default_zones) && in_array($key, $default_zones)) ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
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
                <label class="col-md-2"
                    for="experience_interval">{{ __('static.provider.experience_interval') }}<span> *</span></label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control" name="experience_interval" id="experience_interval"
                        data-placeholder="{{ __('static.provider.select_experience_interval') }}">
                        <option class="select-placeholder" value=""></option>
                        @foreach (['years' => 'Years', 'months' => 'Months'] as $key => $option)
                            <option class="option" value="{{ $key }}"
                                @if (old('experience_interval', isset($provider) ? $provider->experience_interval : '') == $key) selected @endif>{{ $option }}</option>
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
                <label class="col-md-2" for="experience">{{ __('static.provider.experience_duration') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <input class="form-control" min="0" type="number" name="experience_duration"
                        id="experience_duration"
                        value="{{ isset($provider->experience_duration) ? $provider->experience_duration : old('experience_duration') }}"
                        placeholder="{{ __('static.provider.enter_experience_duration') }}">
                </div>
                @error('experience')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class='form-group row'>
                <label class="col-md-2" for="known_languages">{{ __('static.users.known_languages') }}</label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control language"
                        data-placeholder="{{ __('static.users.select_languages') }}" id="known_languages"
                        name="known_languages[]" multiple>
                        <option value=""></option>
                        @foreach ($languages as $language)
                            <option value="{{ $language->id }}"
                                @if (is_array(old('known_languages')) && in_array($language->id, old('known_languages'))) selected
                                @elseif (isset($default_languages) && in_array($language->id, $default_languages))
                                    selected @endif>
                                {{ $language->key }}
                            </option>
                        @endforeach
                    </select>
                    @error('known_languages')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            @isset($provider)
                <div class='form-group row'>
                    <label class="col-md-2" for="expertiseIN">{{ __('static.users.expertise_in') }}</label>
                    <div class="col-md-10 error-div select-dropdown">
                        <select class="select-2 form-control language"
                            data-placeholder="{{ __('static.users.select_services') }}" id="expertiseIN"
                            name="expertiseIN[]" multiple>
                            <option value=""></option>
                            @foreach ($services as $key => $value)
                                <option value="{{ $key }}"
                                    @if (isset($provider->expertise)) @if (in_array($key, $default_services)) selected @endif
                                @elseif(old('expertiseIN.' . $key) == $key) selected @endif>
                                    {{ $language->key }}
                                </option>
                            @endforeach
                        </select>
                        @error('expertiseIN')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            @endisset
            @if (Request::is('backend/provider/create'))
                <div class="form-group row">
                    <label class="col-md-2" for="name">{{ __('static.password') }}<span> *</span></label>
                    <div class="col-md-10">
                        <div class="position-relative">
                            <input class="form-control" type="password" name="password" id="password"
                                value="{{ old('password') }}" placeholder="{{ __('static.users.enter_password') }}"
                                autocomplete="off">
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
                    <label class="col-md-2" for="name">{{ __('static.confirm_password') }}<span> *</span></label>
                    <div class="col-md-10">
                        <div class="position-relative">
                            <input class="form-control" id="confirm_password" type="password"
                                name="confirm_password" autocomplete="off" value="{{ old('password') }}"
                                placeholder="{{ __('static.users.re_enter_password') }}">
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
            @endif
            <div class="form-group row">
                <label class="col-md-2" for="role">{{ __('static.status') }}</label>
                <div class="col-md-10">
                    <div class="editor-space">
                        <label class="switch">
                            @if (isset($provider))
                                <input class="form-control" type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1"
                                    {{ $provider->status ? 'checked' : '' }}>
                            @else
                                <input class="form-control" type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1"
                                    checked>
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
        <div class="tab-pane fade" id="bank_details" role="tabpanel" aria-labelledby="bank_details-tab">
            <div class="form-group row">
                <label class="col-md-2" for="bank_name">{{ __('static.bank_details.bank_name') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <input class="form-control" type="text" name="bank_name" id="bank_name"
                        value="{{ isset($provider->bankDetail->bank_name) ? $provider->bankDetail->bank_name : old('bank_name') }}"
                        placeholder="{{ __('static.bank_details.enter_bank_name') }}">
                    @error('bank_name')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="holder_name">{{ __('static.bank_details.holder_name') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <input class="form-control" type="text" name="holder_name" id="holder_name"
                        value="{{ isset($provider->bankDetail->holder_name) ? $provider->bankDetail->holder_name : old('holder_name') }}"
                        placeholder="{{ __('static.bank_details.enter_holder_name') }}">
                    @error('holder_name')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="account_number">{{ __('static.bank_details.account_number') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <input class="form-control" type="number" name="account_number" id="account_number"
                        value="{{ isset($provider->bankDetail->account_number) ? $provider->bankDetail->account_number : old('account_number') }}"
                        placeholder="{{ __('static.bank_details.enter_account_number') }}">
                    @error('account_number')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="branch_name">{{ __('static.bank_details.branch_name') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <input class="form-control" type="text" name="branch_name" id="branch_name"
                        value="{{ isset($provider->bankDetail->branch_name) ? $provider->bankDetail->branch_name : old('branch_name') }}"
                        placeholder="{{ __('static.bank_details.enter_branch_name') }}">
                    @error('branch_name')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="ifsc_code">{{ __('static.bank_details.ifsc_code') }}</label>
                <div class="col-md-10">
                    <input class="form-control" type="text" name="ifsc_code" id="ifsc_code"
                        value="{{ isset($provider->bankDetail->ifsc_code) ? $provider->bankDetail->ifsc_code : old('ifsc_code') }}"
                        placeholder="{{ __('static.bank_details.enter_ifsc_code') }}">
                    @error('ifsc_code')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="swift_code">{{ __('static.bank_details.swift_code') }}<span>
                        *</span></label>
                <div class="col-md-10">
                    <input class="form-control" type="text" name="swift_code" id="swift_code"
                        value="{{ isset($provider->bankDetail->swift_code) ? $provider->bankDetail->swift_code : old('swift_code') }}"
                        placeholder="{{ __('static.bank_details.enter_swift_code') }}">
                    @error('swift_code')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="previousBtn btn cancel">{{ __('static.previous') }}</button>
                <button type="button" class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
            </div>
        </div>
        @if (!isset($provider) || (isset($provider) && $provider->type == 'company'))
            <div class="tab-pane fade" id="company_details" role="tabpanel" aria-labelledby="company-details-tab">
                <div class="form-group row">
                    <label for="image" class="col-md-2">{{ __('static.provider.company_logo') }}<span>
                            *</span></label>
                    <div class="col-md-10">
                        <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="company_logo"
                            name="company_logo">
                        @error('company_logo')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                @if (isset($provider) &&
                        isset($provider->company) &&
                        isset($provider->company->getFirstMedia('company_logo')->original_url))
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-10">
                                <div class="image-list">
                                    <div class="image-list-detail">
                                        <div class="position-relative">
                                            <img src="{{ $provider->company->getFirstMedia('company_logo')->original_url }}"
                                                id="{{ $provider->company->getFirstMedia('company_logo')->id }}"
                                                alt="User Image" class="image-list-item">
                                            <div class="close-icon">
                                                <i data-feather="x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endisset
                <div class="form-group row">
                    <label class="col-md-2" for="company_name">{{ __('static.provider.company_name') }}<span>
                            *</span></label>
                    <div class="col-md-10">
                        <input class='form-control' type="text" name="company_name" id="company_name"
                            value="{{ isset($provider->company->name) ? $provider->company->name : old('company_name') }}"
                            placeholder="{{ __('static.provider.enter_company_name') }}">
                        @error('company_name')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2" for="company_email">{{ __('static.provider.company_email') }}<span>
                            *</span></label>
                    <div class="col-md-10">
                        <input class='form-control' type="email" name="company_email" id="company_email"
                            value="{{ isset($provider->company->email) ? $provider->company->email : old('company_email') }}"
                            placeholder="{{ __('static.provider.enter_company_email') }}">
                        @error('company_email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2" for="phone">{{ __('static.provider.company_phone') }}<span>
                            *</span></label>
                    <div class="col-md-10">
                        <div class="input-group mb-3 phone-detail">
                            <div class="col-sm-1">
                                <select class="select-2 form-control select-country-code" name="company_code"
                                    data-placeholder="">
                                    @php
                                        $default = old(
                                            'company_code',
                                            $provider->company->code ?? App\Helpers\Helpers::getDefaultCountryCode(),
                                        );
                                    @endphp
                                    <option value="" selected></option>
                                    @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                                        <option class="option" value="{{ $option->phone_code }}"
                                            data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                                            @if ($option->phone_code == $default) selected @endif
                                            data-default="{{ $default }}">
                                            +{{ $option->phone_code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('code')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-sm-11">
                                <input class="form-control" type="number" name="company_phone"
                                    id="company_phone"
                                    value="{{ isset($provider->company->phone) ? $provider->company->phone : old('company_phone') }}"
                                    min="1"
                                    placeholder="{{ __('static.provider.enter_company_phone') }}">
                            </div>
                        </div>
                        @error('phone')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label for="company_description"
                        class="col-md-2">{{ __('static.service.description') }}</label>
                    <div class="col-md-10">
                        <textarea class="form-control" placeholder="{{ __('static.provider.enter_description') }}" id="company_description"
                            rows="4" name="company_description" cols="50">
@isset($provider->company->description){{ $provider->company->description }}@endisset
</textarea>
                        @error('company_description')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="previousBtn btn cancel">{{ __('static.previous') }}</button>
                    <button class="nextBtn btn btn-primary" type="button">{{ __('static.next') }}</button>
                </div>
        </div>
    @endif
    <div class="tab-pane fade {{ session('active_tab') == 'address_tab' ? 'show active' : '' }}"
        id="add_address" role="tabpanel" aria-labelledby="user_address-tab">
        @if (request()->is('backend/provider/create'))
            <div class="form-group row">
                <label for="address" class="col-md-2">{{ __('static.provider.address') }}</label>
                <div class="col-md-10">
                    <textarea class="form-control ui-widget autocomplete-google" placeholder="{{ __('static.provider.enter_address') }}"
                        rows="4" id="address" name="address" cols="50">{{ old('address') }}</textarea>
                    @error('address')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            {{-- <div class="form-group row">
                <label class="col-md-2" for="area">{{ __('static.area') }}<span> *</span></label>
                <div class="col-md-10">
                    <input class="form-control" type="text" name="area" id="area"
                        value="{{ old('area') }}" placeholder="{{ __('static.users.enter_area') }}">
                    @error('area')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div> --}}

            <div class="form-group row">
                <label for="street_address" class="col-md-2">{{ __('static.provider.street_address') }}</label>
                <div class="col-md-10">
                    <input type="text" class="form-control ui-widget" id="street_address_1"
                        name="street_address" placeholder="{{ __('static.provider.enter_street_address') }}"
                        value="{{ @$service->address ? $service->address?->street_address : old('address.street_address') }}">
                </div>
                @error('street_address')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group row">
                <label for="country" class="col-md-2">{{ __('static.users.country') }}<span> *</span></label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control select-country" id="country_id" name="country_id"
                        data-placeholder="{{ __('static.users.select_country') }}">
                        <option class="select-placeholder" value=""></option>
                        @forelse ($countries as $key => $option)
                            <option class="option" value={{ $key }}
                                @if (old('country_id')) @if ($key == old('country_id')) selected @endif
                                @endif> {{ $option }}</option>
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
            <div class="form-group row">
                <label for="country" class="col-md-2">{{ __('static.users.state') }}<span> *</span></label>
                <div class="col-md-10 error-div select-dropdown">
                    <select class="select-2 form-control select-state"
                        data-default-state-id="{{ old('state_id') }}" id="state_id" name="state_id"
                        data-placeholder="{{ __('static.users.select_state') }}">
                        <option class="select-placeholder" value=""></option>
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
                    <input class="form-control" type="text" id="city" name="city"
                        value="{{ old('city') }}" placeholder="{{ __('static.users.enter_city') }}">
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
                    <input class="form-control" type="text" name="postal_code" id="postal_code"
                        value="{{ old('postal_code') }}" placeholder="{{ __('static.users.postal_code') }}">
                    @error('postal_code')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
        @else
            <a href="javascript:void(0)" class="add-more" data-bs-toggle="modal" data-bs-target="#addaddress">
                <h5>Address List</h5>
                <div class="add-more-div">
                    <i data-feather="plus"></i>
                </div>
            </a>
            <div class="address-body custom-scrollbar">
                @isset($provider)
                    <div class="row g-3 @if ($provider->addresses->count() == 0) h-100 @endif">
                        @isset($provider->addresses)
                            @forelse ($provider->addresses as $address)
                                <div class="col-md-6 service-address-box">
                                    <div class="service-address">
                                        <div class="service-add-detail">
                                            <div class="address">
                                                <div class="location-icon">
                                                    <img class="img-fluid"
                                                        src="{{ asset('admin/images/svg/location.svg') }}">
                                                </div>
                                                <div class="address-detail">
                                                    <h4>{{ $address->country->name }}-{{ $address->state->name }}</h4>
                                                    <h5>{{ $address->city }}</h5>
                                                </div>
                                            </div>
                                            <div class="action d-flex align-items-center gap-2">
                                                <a href="javascript:void(0)" class="edit-icon" data-bs-toggle="modal"
                                                    data-bs-target="#editAddress{{ $address->id }}">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                @if (count($provider->addresses) > 1)
                                                    <a href="#confirmationModal{{ $address->id }}"
                                                        data-bs-toggle="modal" class="delete-icon">
                                                        <i data-feather="trash-2"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex flex-column no-data-detail">
                                    <img class="mx-auto d-flex" src="{{ asset('admin/images/no-category.png') }}"
                                        alt="no-image">
                                    <div class="data-not-found">
                                        <span>Address Not Found</span>
                                    </div>
                                </div>
                            @endforelse
                        @endisset
                    </div>
                @endisset
            </div>
        @endif
        <div class="card-footer">
            <button type="button" class="previousBtn btn cancel">{{ __('static.previous') }}</button>
            <button class="btn btn-primary submitBtn spinner-btn"
                type="submit">{{ __('static.submit') }}</button>
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
                                // ('.select-state').attr('data-default-state-id', stateId);
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

                company($("#provider_type").val() || 'company');
                $('#provider_type').change(function() {
                    company($(this).val());
                });

                $("#providerForm").validate({
                    ignore: [],
                    rules: {
                        "type": "required",
                        "name": "required",
                        "email": {
                            required: true,
                            email: true
                        },
                        "image": {
                            accept: "image/jpeg, image/png"
                        },
                        "phone": {
                            "required": true,
                            "minlength": 6,
                            "maxlength": 15
                        },
                        "experience_interval": "required",
                        "experience_duration": "required",
                        "password": {
                            required: isRequiredForEdit,
                            minlength: 8
                        },
                        "confirm_password": {
                            required: isRequiredForEdit,
                            equalTo: "#password",
                            minlength: 8
                        },
                        "bank_name": "required",
                        "holder_name": "required",
                        "account_number": "required",
                        "branch_name": "required",
                        "swift_code": "required",
                        "state_id": "required",
                        "country_id": "required",
                        "postal_code": "required",
                        "city": "required",
                        "area": "required",
                        "company_logo": {
                            required: isCompany && isCompanyLogo,
                            accept: "image/jpeg, image/png"
                        },
                        "company_name": {
                            required: isCompany
                        },
                        "company_email": {
                            required: isCompany
                        },
                        "company_phone": {
                            required: isCompany
                        }
                    },
                    messages: {
                        "image": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                        "company_logo": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });

                $("#addressForm").validate({
                    ignore: [],
                    rules: {
                        "country_id": "required",
                        "state_id": "required",
                        "city": "required",
                        "area": "required",
                        "postal_code": "required",
                    }
                });

            });

            function isCompany(element) {
                return $("#provider_type").val() === "company";
            }

            function isCompanyLogo(element) {
                if (isCompany(element)) {
                    @if (isset($provider) ||
                            (isset($provider->company) && isset($provider->company->getFirstMedia('company_logo')->original_url)))
                        return false;
                    @else
                        return true;
                    @endif
                }
                return false;
            }

            function isRequiredForEdit() {
                return "{{ isset($provider) }}" ? false : true;
            }

            function company(val) {
                if (val === 'company') {
                    $('.nav-tabs a[href="#company_details"]').show();
                } else {
                    $('.nav-tabs a[href="#company_details"]').hide();
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

        })(jQuery);
    </script>
@endpush
