<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
    <div class="col-md-10">
        <ul class="language-list">
            @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                @if(isset($tax))
                    <li>
                        <a href="{{ route('backend.tax.edit', ['tax' => $tax->id, 'locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i data-feather="arrow-up-right"></i></a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('backend.tax.create', ['locale' => $lang->locale]) }}" class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}" -target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}" alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i data-feather="arrow-up-right"></i></a>
                    </li>
                @endif
            @empty
                <li>
                    <a href="{{ route('backend.tax.edit', ['tax' => $tax->id, 'locale' => Session::get('locale', 'en')]) }}" class="language-switcher active" target="blank"><img src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i data-feather="arrow-up-right"></i></a>
                </li>
            @endforelse
        </ul>
    </div>
</div>

<input type="hidden" name="locale" value="{{ request('locale') }}">
<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.name') }} ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" id="name" name="name" value="{{ isset($tax->name) ? $tax->getTranslation('name', request('locale', app()->getLocale())) : old('name') }}" placeholder="{{ __('static.users.enter_name') }} ({{ request('locale', app()->getLocale()) }})">
        @error('name')
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

<div class="form-group row">
    <label class="col-md-2" for="rate">{{ __('static.tax.rate') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <div class="input-group mb-3 flex-nowrap">
            <div class="w-100 percent">
                <input class="form-control" type="number" id="rate" name="rate" min="1" value="{{ isset($tax->rate) ? $tax->rate : old('price') }}" placeholder="{{ __('static.tax.enter_rate') }}" oninput="if (value > 100) value = 100; if (value < 0) value = 0;">
            </div>
            <span class="input-group-text">%</span>
            @error('rate')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>

        <div class="form-group row">
            <label class="col-md-2" for="zone_id">{{ __('static.service.zone') }}<span> *</span></label>
            <div class="col-md-10 error-div">
            <select id="blog_zones" class="select-2 form-control disable-all"
                    name="zone_id" data-placeholder="{{ __('static.zone.select-zone') }}" required>
                <option></option>

                @foreach($zones as $key => $value)
                    <option value="{{ $key }}"
                        {{ old('zone_id', $tax->zone_id ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
                @error('zone_id')
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
                @if (isset($tax))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1" {{ $tax->status ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1" checked>
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>

@push('js')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#taxForm").validate({
                    ignore: [],
                    rules: {
                        "name": "required",
                        "rate": "required",
                    }
                });

                // $('.disable-all').on('change', function() {
                //     const $currentSelect = $(this);
                //     const selectedValues = $currentSelect.val();
                //     const allOption = "all";

                //     if (selectedValues && selectedValues.includes(allOption)) {

                //         $currentSelect.val([allOption]);
                //         $currentSelect.find('option').not(`[value="${allOption}"]`).prop('disabled', true);
                //     } else {

                //         $currentSelect.find('option').prop('disabled', false);
                //     }
                // });
            });
        })(jQuery);
    </script>
@endpush
