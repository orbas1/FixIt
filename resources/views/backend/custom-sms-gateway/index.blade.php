@extends('backend.layouts.master')
@section('title', __('static.custom_sms_gateways.custom_sms_gateways'))
@section('content')
<div class="">
    <form id="smsForm" action="{{ route('backend.custom-sms-gateway.update', @$id) }}" method="POST"
        enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row g-xl-4 g-3">
            <div class="col-xl-9">
                <div class="left-part">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title flip">
                                <h3>{{ __('static.custom_sms_gateways.custom_sms_gateways') }}</h3>
                            </div>
                            <div class="slide">
                                <div class="note">
                                    <span>Are you confuse how to do??
                                        <a href="#documentModal" data-bs-toggle="modal">follow this for reference</a>
                                    </span>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="base_url">{{ __('static.custom_sms_gateways.base_url') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control"
                                            value="{{ isset($settings->base_url) ? $settings->base_url : old('base_url') }}"
                                            type="text" name="base_url"
                                            placeholder="{{ __('static.custom_sms_gateways.enter_base_url') }}"
                                            required>
                                        @error('base_url')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="method">{{ __('static.custom_sms_gateways.method') }}<span>
                                            *</span></label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="select-2 form-control" id="method" name="method"
                                            data-placeholder="{{ __('static.custom_sms_gateways.select_method') }}">
                                            <option class="select-placeholder" value=""></option>
                                            @foreach (['post' => 'POST', 'get' => 'GET'] as $key => $option)
                                            <option class="option" value="{{ $key }}" @if (old('method', $settings->
                                                method ?? '') == $key) selected @endif>
                                                {{ $option }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('method')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="is_config">{{ __('static.custom_sms_gateways.configs') }}<span>
                                            *</span></label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="select-2 form-control" id="is_config" name="is_config[]"
                                            data-placeholder="{{ __('static.custom_sms_gateways.select_is_config') }}"
                                            multiple>
                                            <option class="select-placeholder" value=""></option>
                                            @foreach (['sid' => 'SID', 'auth_token' => 'Auth Token', 'configs' =>
                                            'Custom Keys'] as $key => $option)
                                            <option class="option" value="{{ $key }}" @if (isset($settings->is_config))
                                                @if (in_array($key, $settings->is_config))
                                                selected @endif
                                                @endif>{{ $option }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('is_config')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row sid">
                                    <label class="col-md-2" for="sid">{{ __('static.custom_sms_gateways.sid') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control"
                                            value="{{ isset($settings->sid) ? $settings->sid : old('sid') }}"
                                            type="text" name="sid"
                                            placeholder="{{ __('static.custom_sms_gateways.enter_sid') }}" required>
                                        @error('sid')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row auth_token">
                                    <label class="col-md-2"
                                        for="auth_token">{{ __('static.custom_sms_gateways.auth_token') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control"
                                            value="{{ isset($settings->auth_token) ? $settings->auth_token : old('auth_token') }}"
                                            type="text" name="auth_token"
                                            placeholder="{{ __('static.custom_sms_gateways.enter_auth_token') }}"
                                            required>
                                        @error('auth_token')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="from">{{ __('static.custom_sms_gateways.from') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control"
                                            value="{{ isset($settings->from) ? $settings->from : old('from') }}"
                                            type="number" name="from"
                                            placeholder="{{ __('static.custom_sms_gateways.enter_from') }}" required>
                                        @error('from')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row configs">
                                    <label class="col-md-2"
                                        for="configs">{{ __('static.custom_sms_gateways.custom_keys') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <div class="customKeys">
                                            @if (isset($settings->custom_keys))
                                            @foreach ($settings->custom_keys as $key => $value)
                                            <div class="row mb-2 custom-key">
                                                <div class="col-sm-4">
                                                    <input class="form-control"
                                                        value="{{ isset($key) ? $key : old('key') }}" type="text"
                                                        name="key[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                                </div>
                                                <div class="col-sm-4">
                                                    <input class="form-control"
                                                        value="{{ isset($value) ? $value : old('value') }}" type="text"
                                                        name="value[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                                </div>
                                                <div class="col-sm-4">
                                                    <button type="button" class="delete-key btn btn-danger btn-sm"><i
                                                            data-feather="trash-2"></i></button>
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="row mb-2 custom-key">
                                                <div class="col-sm-4">
                                                    <input class="form-control" value="" type="text" name="key[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                                </div>
                                                <div class="col-sm-4">
                                                    <input class="form-control" value="" type="text" name="value[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                                </div>
                                                <div class="col-sm-4">
                                                    <button type="button" class="delete-key btn btn-danger btn-sm"><i
                                                            data-feather="trash-2"></i></button>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-primary add-custom-key">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title flip">
                                <h3>{{ __('Add Body') }}</h3>
                                <!-- <div class="header-action">
                                        <div class="accordion-btn">
                                            <i class="ri-arrow-down-s-fill"></i>
                                        </div>
                                    </div> -->
                            </div>
                            <div class="slide position-relative">
                                <ul class="nav nav-tabs horizontal-tab custom-scroll mb-sm-4 mb-3" id="account" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile"
                                            type="button" role="tab" aria-controls="profile" aria-selected="true">
                                            <i class="ri-javascript-line"></i>
                                            {{ __('JSON') }}
                                            <i class="ri-error-warning-line danger errorIcon"></i>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address"
                                            type="button" role="tab" aria-controls="address" aria-selected="true">
                                            <i class="ri-file-text-line"></i>
                                            {{ __('Formdata') }}
                                            <i class="ri-error-warning-line danger errorIcon"></i>
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content" id="accountContent">
                                    <div class="tab-pane fade  {{ session('active_tab') != null ? '' : 'show active' }}"
                                        id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <div class="form-group row">
                                            <div class="col-12">
                                                <textarea class="form-control" rows="5" name="body"
                                                    placeholder="{{ __('Enter Text here..') }}" cols="80"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="address" role="tabpanel"
                                        aria-labelledby="address-tab">
                                        <button type="button" name="save" class="btn btn-primary add-body">
                                            {{ __('Add') }}
                                        </button>
                                        <div class="body mt-2">
                                            @if (isset($settings->body) && is_array($settings->body))
                                            @foreach ($settings->body as $key => $body)
                                            <div class="form-group bodies">
                                                <div>
                                                    <input class="form-control"
                                                        value="{{ isset($key) ? $key : old('body_key') }}" type="text"
                                                        name="body_key[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                                </div>
                                                <div>
                                                    <input class="form-control"
                                                        value="{{ isset($body) ? $body : old('body_value') }}"
                                                        type="text" name="body_value[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                                </div>
                                                <div class="h-100">
                                                    <button type="button"
                                                        class="delete-body trash-icon-btn btn" id=""><i
                                                            data-feather="trash-2"></i></button>
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="form-group bodies">
                                                <div>
                                                    <input class="form-control" value="{{ old('body_key') }}"
                                                        type="text" name="body_key[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                                </div>
                                                <div>
                                                    <input class="form-control" value="{{ old('body_value') }}"
                                                        type="text" name="body_value[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                                </div>
                                                <div>
                                                    <button type="button"
                                                        class="w-100 delete-body btn btn-danger btn-sm" id="">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title flip">
                                <h3>{{ __('Add Params') }}</h3>
                                <div class="header-action">
                                    <!-- <div class="accordion-btn">
                                             <i class="iconsax" icon-name="chevron-down"></i>
                                        </div> -->
                                    <button type="button" name="save" class="btn btn-primary add-param">
                                        {{ __('Add') }}
                                    </button>
                                </div>
                            </div>
                            <div class="slide">
                                <div class="params mt-2">
                                    @if (isset($settings->params))
                                    @foreach ($settings->params as $key => $param)
                                    <div class="form-group row g-0 parameters">
                                        <div class="">
                                            <input class="form-control"
                                                value="{{ isset($key) ? $key : old('param_key') }}" type="text"
                                                name="param_key[]"
                                                placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                        </div>
                                        <div class="">
                                            <input class="form-control"
                                                value="{{ isset($param) ? $param : old('param_value') }}"
                                                type="text" name="param_value[]"
                                                placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                        </div>
                                        <div class="h-100">
                                            <button type="button" class="delete-param btn trash-icon-btn">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="col-12">
                                        <div>
                                            <div class="form-group parameters">
                                                <div>
                                                    <input class="form-control" value="{{ old('param_key') }}"
                                                        type="text" name="param_key[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                                </div>
                                                <div>
                                                    <input class="form-control" value="{{ old('param_value') }}"
                                                        type="text" name="param_value[]"
                                                        placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                                </div>
                                                <div class="col-sm-2 col-3 text-end">
                                                    <button type="button"
                                                        class="delete-param btn btn-danger btn-sm w-100" id="">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title flip">
                                <h3>{{ __('Add Headers') }}</h3>
                                <div class="header-action">
                                    <!-- <div class="accordion-btn">
                                            <i class="ri-arrow-down-s-fill"></i>
                                        </div> -->

                                    <button type="button" name="save" class="btn btn-primary add-header">
                                        {{ __('Add') }}
                                    </button>
                                </div>
                            </div>
                            <div class="slide">
                                <!-- <button type="button" name="save" class="btn btn-primary add-header">
                                        {{ __('Add') }}
                                    </button> -->
                                <div class="headers mt-2">
                                    @if (isset($settings->headers))
                                    @foreach ($settings->headers as $key => $header)
                                    <div class="form-group head">
                                        <div>
                                            <input class="form-control"
                                                value="{{ isset($key) ? $key : old('header_key') }}" type="text"
                                                name="header_key[]"
                                                placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                        </div>
                                        <div>
                                            <input class="form-control"
                                                value="{{ isset($header) ? $header : old('header_value') }}" type="text"
                                                name="header_value[]"
                                                placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                        </div>
                                        <div class="h-100">
                                            <button type="button" class="delete-head btn trash-icon-btn">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="form-group head">
                                        <div>
                                            <input class="form-control" value="{{ old('header_key') }}" type="text"
                                                name="header_key[]"
                                                placeholder="{{ __('static.custom_sms_gateways.enter_key') }}">
                                        </div>
                                        <div>
                                            <input class="form-control" value="{{ old('header_value') }}" type="text"
                                                name="header_value[]"
                                                placeholder="{{ __('static.custom_sms_gateways.enter_value') }}">
                                        </div>
                                        <div>
                                            <button type="button" class="delete-head btn btn-danger btn-sm w-100">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="p-sticky">
                    <div class="contentbox">
                        <div class="inside">
                            <div class="contentbox-title ">
                                <h3>{{ __('static.publish') }}</h3>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center gap-2 icon-position">
                                                <button type="submit" name="save" class="btn btn-primary">
                                                    <!-- <i class="ri-save-line text-white lh-1"></i> -->
                                                    <i data-feather="save"></i>
                                                    {{ __('static.save') }}
                                                </button>

                                                <button type="button" name="send_test_sms" class="btn btn-primary"
                                                    data-bs-toggle="modal" data-bs-target="#sendTestSMSModal">
                                                    <i data-feather="save"></i>
                                                    <!-- <i class="ri-save-line text-white lh-1"></i> -->
                                                    {{ __('static.custom_sms_gateways.send_test_sms') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="sendTestSMSModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendTestSMSModalLabel">
                        {{ __('static.custom_sms_gateways.send_test_sms') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <form action="{{ route('backend.custom-sms-gateway.test') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3 form-group">
                            <label for="phoneNumber"
                                class="form-label">{{ __('static.custom_sms_gateways.phone_number') }}</label>
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                                placeholder="{{ __('static.custom_sms_gateways.enter_phone_number') }}">
                        </div>
                        <div class="form-group">
                            <label for="testMessage"
                                class="form-label">{{ __('static.custom_sms_gateways.message') }}</label>
                            <textarea class="form-control" id="testMessage" name="testMessage"
                                rows="3">{{ __('static.custom_sms_gateways.test_sms_message') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('static.custom_sms_gateways.close') }}</button>
                        <button type="submit"
                            class="btn btn-primary">{{ __('static.custom_sms_gateways.send_sms') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade document-view-modal" id="documentModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    (function($) {
        "use strict";

        $(document).ready(function() {

            // First, hide all fields
            $('.sid').hide();
            $('.configs').hide();
            $('.auth_token').hide();

            // Check initial state based on settings
            var initialValues = $('#is_config').val();
            if (initialValues) {
                initialValues.forEach((value) => {
                    $('.' + value).show();
                });
            }

            $('#is_config').on('change', function(e) {
                var values = $(this).val();

                // First, hide all fields
                $('.sid').hide();
                $('.configs').hide();
                $('.auth_token').hide();

                if (values) {
                    values.forEach((value) => {
                        $('.' + value).show();
                    });
                }
            });

            $('.add-param').on('click', function() {
                var clonedOption = $('.params .parameters:first').clone().addClass('cloned');
                clonedOption.find('input').val('');
                $('.params').append(clonedOption);
            });

            $('.params').on('click', '.delete-param', function() {
                $(this).closest('.parameters').remove();
            });

            $('.add-header').on('click', function() {
                var clonedOption = $('.headers .head:first').clone();
                clonedOption.find('input').val('');
                $('.headers').append(clonedOption);
            });

            $('.headers').on('click', '.delete-head', function() {
                $(this).closest('.head').remove();
            });

            $('.add-custom-key').on('click', function() {
                var clonedOption = $('.customKeys .custom-key:first').clone().addClass('cloned');
                clonedOption.find('input').val('');
                $('.customKeys').append(clonedOption);
            });

            $('.customKeys').on('click', '.delete-key', function() {
                $(this).closest('.custom-key').remove();
            });

            $('.add-body').on('click', function() {
                var clonedOption = $('.body .bodies:first').clone().addClass('cloned');
                clonedOption.find('input').val('');
                $('.body').append(clonedOption);
            });

            $('.body').on('click', '.delete-body', function() {
                $(this).closest('.bodies').remove();
            });

        });

    })(jQuery);
</script>
@endpush
