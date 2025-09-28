<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.name') }} <span class="required-span">*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" name="name" id="name"
            value="{{ isset($plan->name) ? $plan->name : old('name') }}"
            placeholder="{{ __('static.users.enter_name') }}">
        @error('name')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="product_id">{{ __('static.product_id') }} <span class="required-span">*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" name="product_id" id="product_id"
            value="{{ isset($plan->product_id) ? $plan->product_id : old('product_id') }}"
            placeholder="{{ __('static.users.enter_product_id') }}">
        @error('product_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="max_services">{{ __('static.plan.max_services') }} <span
            class="required-span">*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" name="max_services" id="max_services"
            value="{{ $plan->max_services ?? old('max_services') }}"
            placeholder="{{ __('static.plan.enter_max_services') }}">
        @error('max_services')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="service_packages">{{ __('static.plan.service_packages') }} <span
            class="required-span">*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" name="max_service_packages" id="max_service_packages"
            value="{{ $plan->max_service_packages ?? old('max_service_packages') }}"
            placeholder="{{ __('static.plan.enter_max_service_packages') }}">
        @error('max_service_packages')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="service_packages">{{ __('static.plan.max_addresses') }}</label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" name="max_addresses" id="max_addresses"
            value="{{ $plan->max_service_packages ?? old('max_service_packages') }}"
            placeholder="{{ __('static.plan.enter_max_addresses') }}">
        @error('enter_max_addresses')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="service_packages">{{ __('static.plan.servicemen') }} <span
            class="required-span">*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" name="max_servicemen" id="max_servicemen"
            value="{{ $plan->max_servicemen ?? old('max_servicemen') }}"
            placeholder="{{ __('static.plan.enter_max_servicemen') }}">
        @error('max_servicemen')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="duration">{{ __('static.plan.duration') }} <span
            class="required-span">*</span></label>
    <div class="col-md-10 error-div">
        <select class="select-2 form-control" id="duration" name="duration"
            data-placeholder="{{ __('static.plan.select_duration') }}">
            <option class="select-placeholder" value=""></option>
            @foreach (['monthly' => 'Monthly', 'yearly' => 'Yearly'] as $key => $option)
                <option class="option" value="{{ $key }}" @if (old('duration', $plan->duration ?? old('duration')) == $key) selected @endif>
                    {{ $option }}</option>
            @endforeach
        </select>
        @error('duration')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row amount">
    <label class="col-md-2" for="price">{{ __('static.service.price') }} <span
            class="required-span">*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" name="price" id="price"
            value="{{ $plan->price ?? old('price') }}" placeholder="{{ __('static.plan.enter_plan_price') }}">
        @error('price')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="description" class="col-md-2">{{ __('static.blog.description') }} <span
            class="required-span">*</span></label>
    <div class="col-md-10">
        <textarea class="form-control" id="description" rows="4" placeholder="{{ __('static.enter_description') }}"
            name="description" cols="50">{{ isset($plan->description) ? $plan->description : old('description') }}</textarea>
        @error('description')
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
                @if (isset($plan))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $plan->status ? 'checked' : '' }}>
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

<div class="text-end">
    <button class="btn btn-primary spinner-btn ms-auto" type="submit">{{ __('static.submit') }}</button>
</div>

@push('js')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#planForm").validate({
                    ignore: [],
                    rules: {
                        "name": "required",
                        "max_services": "required",
                        "max_service_packages": "required",
                        "max_servicemen": "required",
                        "duration": "required",
                        "price": "required",
                        "description": "required",
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
