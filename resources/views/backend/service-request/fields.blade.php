@use('app\Helpers\Helpers')

<div class="form-group row">
    <label for="images" class="col-md-2">{{ __('static.service.images') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="file" id="images[]" name="images[]" multiple>
        @error('images')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@if (isset($Request->media) && !$Request->media->isEmpty())
    <div class="form-group">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-10">
                <div class="image-list">
                    @foreach ($Request->getMedia('image') as $media)
                        <div class="image-list-detail">
                            <div class="position-relative">
                                <img src="{{ $media->original_url }}" id="{{ $media->id }}" alt="User Image"
                                    class="image-list-item">
                                <div class="close-icon">
                                    <i data-feather="x"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<div class="form-group row">
    <label class="col-md-2" for="title">{{ __('static.title') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" id="title" name="title" value="{{ isset($Request->title) ? $Request->title : old('title') }}" placeholder="{{ __('static.service.enter_title') }}">
        @error('title')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label for="description" class="col-md-2">{{ __('static.service.description') }}</label>
    <div class="col-md-10">
        <textarea class="form-control" rows="4" name="description" placeholder="{{ __('static.service.enter_description') }}" cols="50">{{ $Request->description ?? old('description') }}</textarea>
        @error('description')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="duration">{{ __('static.service.duration') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="number" min="1" name="duration" id="duration"
            value="{{ isset($Request->duration) ? $Request->duration : old('duration') }}"
            placeholder="{{ __('static.service.enter_duration') }}">
        @error('duration')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2" for="duration_unit">{{ __('static.service.duration_unit') }}<span>
            *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control" id="duration_unit" name="duration_unit"
            data-placeholder="{{ __('static.service.select_duration_unit') }}">
            <option class="select-placeholder" value=""></option>
            @foreach (['hours' => 'Hours', 'minutes' => 'Minutes'] as $key => $option)
                <option class="option" value="{{ $key }}" @if (old('duration_unit', $Request->duration_unit ?? '') === $key) selected @endif>
                    {{ $option }}</option>
            @endforeach
        </select>
        @error('duration_unit')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-md-2"
        for="required_servicemen">{{ __('static.service.required_servicemen') }}<span>*</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" id="required_servicemen" name="required_servicemen"
            value="{{ isset($Request->required_servicemen) ? $Request->required_servicemen : old('required_servicemen') }}"
            placeholder="{{ __('static.service.enter_required_servicemen') }}">
        @error('required_servicemen')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="price">{{ __('static.service.price') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <div class="input-group mb-3 flex-nowrap">
            <span class="input-group-text">{{ Helpers::getSettings()['general']['default_currency']->symbol }}</span>
            <div class="w-100">
                <input class='form-control' type="number" id="price" name="price" min="1"
                    value="{{ isset($Request->initial_price) ? $Request->initial_price : old('price') }}"
                    placeholder="{{ __('static.coupon.price') }}">
                @error('price')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="category_id">{{ __('static.service.category') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select id="category_id[]" class="select-2 form-control categories disable-all" data-placeholder="{{ __('static.service.select_categories') }}" search="true" name="category_id[]" multiple>
            <option value=""></option>
            <option value="all">{{ __('static.report.select_all') }}</option>
            @foreach ($categories as $key => $value)
                <option value="{{ $key }}"
                    @if (isset($default_categories) && in_array($key, $default_categories)) selected
                            @elseif (old('category_id') && in_array($key, old('category_id'))) selected @endif>
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

<div class="text-end">
    <button class="btn btn-primary submitBtn spinner-btn ms-auto" type="submit">{{ __('static.submit') }}</button>
</div>

@push('js')
    <script>
    $(document).ready(function() {
        $("#serviceRequestForm").validate({

            rules: {
                "title": "required",
                "category_id[]": "required",
                "required_servicemen": "required",
                "price": "required",
                "duration": "required",
                "duration_unit": "required",
                "images[]": {
                    required: isServiceRequestImage,
                    accept: "image/jpeg, image/png"
                },
            },
            messages: {
                "images[]": {
                    accept: "Only JPEG and PNG files are allowed.",
                },
            }
        });

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


        function isServiceRequestImage() {
            @if (isset($Request->media) && !$Request->media->isEmpty())
                return false;
            @else
                return true;
            @endif
        }
    });
    </script>
@endpush
