<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.language.name') }}<span> *</span></label>
    <div class="col-md-10">
        <div class="input-group mb-3 phone-detail">
            <div class="col-sm-3">
                <select id="select-country" class="form-control form-select form-select-transparent" name="flag" data-placeholder="Select Flag">
                    <option></option>
                    @foreach (App\Helpers\Helpers::getCountryCodes() as $key => $option)
                        <option value="{{ $option->flag }}" image="{{ asset('admin/images/flags/' . $option->flag) }}" {{ @$language?->flag == asset('admin/images/flags/' . $option->flag) ? 'selected' : '' }}>{{ $option['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <input class="form-control" type="text" name="name" value="{{ isset($language->name) ? $language->name : old('name') }}" placeholder="{{ __('static.language.enter_language_name') }}">
            @error('name')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="locale">{{ __('static.language.locale') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <select class="select-2 form-control" name="locale" data-placeholder="{{ __('static.language.select_locale') }}">
            <option></option>
            @foreach (config('enums.code_locales') as $key => $locale)
                @if ($locale != @$language?->locale)
                    <option class="option" @selected(old('locale', @$language->locale) == $key) value="{{ $key }}">
                        {{ $locale }}
                    </option>
                @endif
            @endforeach
        </select>
        @error('locale')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="app_locale">{{ __('static.language.app_locale') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <select class="select-2 form-control" name="app_locale" data-placeholder="{{ __('static.language.select_app_locale') }}">
            <option></option>
            @foreach (config('enums.app_locales') as $key => $locale)
                @if ($locale != @$language?->locale)
                    <option class="option" @selected(old('app_locale', @$language->app_locale) == $key) value="{{ $key }}">
                        {{ $locale }}
                    </option>
                @endif
            @endforeach
        </select>
        @error('app_locale')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.language.is_rtl') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                <input class="form-control" type="hidden" name="is_rtl" value="0">
                <input class="form-check-input" type="checkbox" name="is_rtl" id="" value="1"
                    @checked(@$language?->is_rtl)>
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
                <input class="form-control" type="hidden" name="status" value="0">
                <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                    @checked(@$language?->status)>
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>

<div class="text-end">
    <button id='submitBtn' type="submit" class="btn btn-primary ms-auto spinner-btn">{{ __('static.submit') }}</button>
</div>


@push('js')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#languageForm").validate({
                    ignore: [],
                    rules: {
                        "name": "required",
                        "locale": "required",
                        "app_locale": "required",
                    }
                });
            });
const optionFormat = (item) => {
    if (!item.id) return item.text;

    const imgSrc = item.element.getAttribute('image');
    const text = item.text || item.element.textContent || '';

    const $container = $(`
        <div class="d-flex align-items-center">
            <img src="${imgSrc}" style="width: 20px; height: 15px; margin-right: 8px;" />
            <span>${text}</span>
        </div>
    `);

    return $container;
};
            $('#select-country').select2({
                placeholder: "Select an option",
                templateSelection: optionFormat,
                templateResult: optionFormat
            });

        })(jQuery);
    </script>
@endpush
