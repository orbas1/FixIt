<div class="form-group row">
    <label class="col-md-2" for="image">{{ __('static.provider.image') }}</label>
    <div class="col-md-10">
        <input class="form-control" type="file" accept=".jpg, .png, .jpeg" name="image" value="image">
        @error('image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@isset($user?->getFirstMedia('image')->original_url)
    <div class="form-group">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-10">
                <div class="image-list">
                    <div class="image-list-detail">
                        <div class="position-relative">
                            <img src="{{ $user?->getFirstMedia('image')->original_url }}" id="{{ $user?->getFirstMedia('image')->id }}" alt="User Image"
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
@endisset

<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.name') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="text" id="name" name="name"
            value="{{ isset($user->name) ? $user->name : old('name') }}"
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
        <input class="form-control" type="text" id="email" name="email"
            value="{{ isset($user->email) ? $user->email : old('email') }}"
            placeholder="{{ __('static.users.enter_email') }}" autocomplete="off">
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
                <select class="select-2 form-control select-country-code" id="select-country-code" name="code"
                    data-placeholder="">
                    @php
                        $default = old('code', $user->code ?? App\Helpers\Helpers::getDefaultCountryCode());
                    @endphp
                    <option value="" selected></option>
                    @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                        <option class="option" value="{{ $option->phone_code }}"
                            data-image="{{ asset('admin/images/flags/' . $option->flag) }}"
                            @if ($option->phone_code == $default) selected @endif data-default="{{ $default }}">
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
                <input class="form-control phone-fill-input" type="number" name="phone" id="phone"
                    value="{{ isset($user->phone) ? $user->phone : old('phone') }}"
                    placeholder="{{ __('static.serviceman.enter_phone_number') }}" maxlength="15" oninput="this.value = this.value.slice(0, 15);" min="1" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
            </div>
        </div>
        @error('phone')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if (Request::is('backend/user/create'))
    <div class="form-group row">
        <label class="col-md-2" for="password">{{ __('static.password') }}<span> *</span></label>
        <div class="col-md-10">
            <div class="position-relative">
                <input class="form-control" id="password" type="password" name="password" autocomplete="off"
                    value="{{ old('password') }}" placeholder="{{ __('static.users.enter_password') }}">
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
        <label class="col-md-2" for="confirm_password">{{ __('static.confirm_password') }}<span> *</span></label>
        <div class="col-md-10">
            <div class="position-relative">
                <input class="form-control" id="confirm_password" type="password" name="confirm_password"
                    value="{{ old('confirm_password') }}" autocomplete="off"
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

<div class="form-group row ">
    <label class="col-md-2" for="role">{{ __('static.roles.role') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control" name="role" id="role"
            data-placeholder="{{ __('static.roles.select_role') }}">
            <option class="select-placeholder" value=""></option>
            @foreach ($roles as $key => $option)
                <option class="option" value="{{ $key }}" @if (old('role', isset($user) ? $user->roles->first()->id : null) == $key) selected @endif>
                    {{ $option }}</option>
            @endforeach
        </select>
        @error('role')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($user))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $user->status ? 'checked' : '' }}>
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
    <script>
        $('.select-2').select2();
        $("#userForm").validate({
            ignore: [],
            rules: {
                "name": {
                    required: true
                },
                "email": {
                    required: true,
                    email: true
                },
                "phone": {
                    "required": true,
                    "minlength": 6,
                    "maxlength": 15
                },
                "image": {
                    accept: "image/jpeg, image/png"
                },
                "role": {
                    required: true
                },
                "password": {
                    required: true,
                    minlength:8
                },
                "confirm_password": {
                    required: true,
                    equalTo: "#password",
                    minlength:8
                },
            },
            messages: {
                "image": {
                    accept: "Only JPEG and PNG files are allowed.",
                },
            }
        });
        $('.select-2').on('select2:close', function(e) {
            $(this).valid();
        });
    </script>
@endpush
