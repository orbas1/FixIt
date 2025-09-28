@use('App\Enums\SymbolPositionEnum')
<div class="form-group row">
    <label for="image" class="col-md-2">{{ __('static.provider-document.image') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="file" accept=".jpg, .png, .jpeg" id="image" name="image">
        @error('image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@if (isset($currency) && isset($currency->getFirstMedia('currency')->original_url))
    <div class="form-group">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-10">
                <div class="image-list">
                    <div class="image-list-detail">
                        <div class="position-relative">
                            <img src="{{ $currency->getFirstMedia('currency')->original_url }}"
                                id="{{ $currency->getFirstMedia('currency')->original_url }}" alt="User Image"
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
    <label class="col-md-2" for="code">{{ __('static.currency.code') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <select class="select-2 form-control" id="code" name="code"
            data-placeholder="{{ __('static.currency.select_code') }}">
            <option class="select-placeholder" value=""></option>
            @foreach ($code as $key => $option)
                <option class="option" value="{{ $key }}" @if (old('code', isset($currency) ? $currency->code : '') == $key) selected @endif>
                    {{ $option }}</option>
            @endforeach
        </select>
        @error('code')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="symbol">{{ __('static.currency.symbol') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" id="symbol" name="symbol"
            value="{{ isset($currency->symbol) ? $currency->symbol : old('symbol') }}"
            placeholder="{{ __('static.currency.enter_symbol') }}" readonly>
        @error('symbol')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@php
    $selected = old('symbol_position', $currency->symbol_position->value ?? '');
@endphp
<div class="form-group row">
    <label class="col-md-2" for="symbol_position">{{ __('static.currency.symbol_position') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <select class="select-2 form-control" id="symbol_position" name="symbol_position" data-placeholder="{{ __('static.currency.select_symbol_position') }}">
            <option class="select-placeholder" value=""></option>
            @foreach (SymbolPositionEnum::cases() as $position)
                <option class="option" value="{{ $position->value }}" @if (old('symbol_position', $selected ?? '') === $position->value) selected @endif>{{ $position->label() }}</option>
            @endforeach
        </select>
        @error('symbol_position')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="no_of_decimal">{{ __('static.currency.decimal_number') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' id="no_of_decimal" type="number" name="no_of_decimal"
            value="{{ isset($currency->no_of_decimal) ? $currency->no_of_decimal : old('no_of_decimal') }}"
            placeholder="{{ __('static.currency.enter_number_of_decimal') }}">
        @error('no_of_decimal')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="exchange_rate">{{ __('static.currency.exchange_rate') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" name="exchange_rate" id="exchange_rate"
            value="{{ isset($currency->exchange_rate) ? $currency->exchange_rate : old('exchange_rate') }}"
            placeholder="{{ __('static.currency.enter_exchange_rate') }}">
        @error('exchange_rate')
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
                @if (isset($currency))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $currency->status ? 'checked' : '' }}>
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
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#currencyForm").validate({
                    ignore: [],
                    rules: {
                        "title": "required",
                        "symbol": "required",
                        "no_of_decimal": "required",
                        "exchange_rate": "required",
                        "code": "required",
                        "image": {
                            required: isCurrencyImages,
                            accept: "image/jpeg, image/png"
                        },
                    },
                    messages: {
                        "image": {
                            accept: "Only JPEG and PNG files are allowed.",
                        },
                    }
                });
                var currencySelect = $('select[name="code"]');
                var symbolInput = $('input[name="symbol"]');
                currencySelect.on('change', function() {
                    var selectedCode = currencySelect.val();

                    if (selectedCode !== null) {
                        $.ajax({
                            url: '{{ route('backend.get-symbol') }}',
                            method: 'GET',
                            data: {
                                code: selectedCode
                            },
                            success: function(response) {
                                symbolInput.val(response.symbol);
                            },
                            error: function() {
                                toastr.error('Failed to fetch symbol.', 'Error');
                            }
                        });
                    } else {
                        symbolInput.val('');
                    }
                });
            });

            function isCurrencyImages() {
                @if (isset($currency->media) && !$currency->media->isEmpty())
                    return false;
                @else
                    return true;
                @endif
            }
        })(jQuery);
    </script>
@endpush
