<div class="tab2-card">
    <ul class="nav nav-tabs" id="providerTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ session('active_tab') != null ? '' : 'show active' }}" id="provider-tab"
                data-bs-toggle="tab" href="#provider" role="tab" aria-controls="provider" aria-selected="true"
                data-tab="0">
                <i data-feather="settings"></i> {{ __('static.provider.provider_details') }}
            </a>
        </li>
    </ul>
</div>

<div class="tab-content" id="providerTabContent">
    <div class="tab-pane fade {{ session('active_tab') != null ? '' : 'show active' }}" id="provider" role="tabpanel" aria-labelledby="provider-tab">
        <div class="row g-md-4 g-3">
            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="type">{{ __('static.provider.type') }}<span> *</span></label>
                    <div class="col-12 error-div select-dropdown">
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
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label for="image" class="col-12">{{ __('static.provider.image') }}</label>
                    <div class="col-12">
                        <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="provider-file-name"
                            name="image">
                        @error('image')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="name">{{ __('static.name') }}<span> *</span></label>
                    <div class="col-12">
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
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="email">{{ __('static.email') }}<span> *</span></label>
                    <div class="col-12">
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
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="phone">{{ __('static.phone') }}<span> *</span></label>
                    <div class="col-12">
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
            </div>

            @if (Request::is('backend/become-provider'))
                <div class="col-xl-4 col-md-6">
                    <div class="form-group row">
                        <label class="col-12" for="name">{{ __('static.password') }}<span> *</span></label>
                        <div class="col-12">
                            <div class="position-relative">
                                <input class="form-control" type="password" name="password" id="password"
                                    value="{{ old('password') }}"
                                    placeholder="{{ __('static.users.enter_password') }}" autocomplete="off">
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
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="form-group row">
                        <label class="col-12" for="name">{{ __('static.confirm_password') }}<span> *</span></label>
                        <div class="col-12">
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
                </div>
            @endif

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="role">{{ __('static.roles.role') }}<span> *</span></label>
                    <div class="col-12 error-div select-dropdown">
                        <select class="select-2 form-control" id="role" name="role" data-placeholder="{{ __('static.roles.select_role') }}">
                            <option value=""></option>
                            @foreach (['provider' => 'Provider', 'serviceman' => 'serviceman'] as $key => $option)
                                <option class="option" value="{{ $key }}"
                                    @if (old('role', isset($provider) ? $provider->type : '') == $key) selected @endif>{{ $option }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="zones">{{ __('static.zone.zones') }}<span> *</span> </label>
                    <div class="col-12 error-div select-dropdown">
                        <select id="blog_zones" class="select-2 form-control" id="zones[]" search="true"
                            name="zones[]" data-placeholder="{{ __('static.zone.select-zone') }}" multiple>
                            <option></option>
                            @foreach ($zones as $key => $value)
                                <option value="{{ $key }}" {{ (is_array(old('zones')) && in_array($key, old('zones'))) || (isset($default_zones) && in_array($key, $default_zones)) ? 'selected' : '' }}>
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
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="form-group row">
                    <label class="col-12" for="category_id">{{ __('static.categories.category') }}</label>
                    <div class="col-12 error-div select-dropdown">
                        <select id="category_id" class="select-2 form-control" id="category_id" search="true" name="category_id" data-placeholder="{{ __('static.categories.select-category') }}">
                            <option></option>
                            @foreach ($categories as $key => $value)
                                <option value="{{ $key }}" {{ (is_array(old('zones')) && in_array($key, old('zones'))) || (isset($default_zones) && in_array($key, $default_zones)) ? 'selected' : '' }}>
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
            </div>

            <div class="col-xl-4 col-md-6 serviceman-only d-none">
                <div class="form-group row">
                    <label class="col-12" for="provider_id">{{ __('static.provider.provider') }}<span> *</span></label>
                    <div class="col-12 error-div select-dropdown">
                        <select class="select-2 form-control" name="provider_id" id="provider_id" data-placeholder="{{ __('static.serviceman.select-provider') }}">
                            <option value=""></option>
                        </select>
                        @error('provider_id')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="footer">
                    <button class="btn btn-primary submitBtn spinner-btn" type="submit">{{ __('static.submit') }}</button>
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
                $("#providerForm").validate({
                    ignore: [],
                    rules: {
                        "type": "required",
                        "role": "required",
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
                        "password": {
                            required: isRequiredForEdit,
                            "minlength": 8,
                        },
                        "confirm_password": {
                            required: isRequiredForEdit,
                            equalTo: "#password",
                            "minlength": 8,
                        },
                    },
                    messages: {
                        "image": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });
            });

            function isRequiredForEdit() {
                return "{{ isset($provider) }}" ? false : true;
            }

            toggleFields($('#provider_type').val(), $('#role').val());

            $('#provider_type').on('change', function () {
                toggleFields($(this).val(), $('#role').val());
            });

            $('#role').on('change', function () {
                toggleFields($('#provider_type').val(), $(this).val());
            });

            $('#category_id').on('change', function () {
                let categoryId = $(this).val();

                if (categoryId) {
                    $.ajax({
                        url: "{{ route('providers.byCategory') }}",
                        method: "GET",
                        data: { category_id: categoryId },
                        success: function (res) {
                            let providerSelect = $('#provider_id');
                            providerSelect.empty().append('<option value=""></option>');

                            $.each(res.providers, function (key, value) {
                                providerSelect.append(`<option value="${key}">${value}</option>`);
                            });
                        }
                    });
                }
            });

            function toggleFields(providerType, role) {
                if (role === 'serviceman') {
                    $('#category_id').closest('.col-md-6').removeClass('d-none');
                    $('#provider_id').closest('.col-md-6').removeClass('d-none');
                    $('#blog_zones').closest('.col-md-6').addClass('d-none');
                } else {
                    $('#category_id').closest('.col-md-6').addClass('d-none');
                    $('#provider_id').closest('.col-md-6').addClass('d-none');
                    $('#blog_zones').closest('.col-md-6').removeClass('d-none');
                }
            }
            

        })(jQuery);
    </script>
@endpush
