<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.name') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" name="name" id="name"
            value="{{ isset($testimonial->name) ? $testimonial->name : old('name') }}"
            placeholder="{{ __('static.testimonials.enter_name') }}">
        @error('name')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="address" class="col-md-2">{{ __('static.testimonials.description') }}<span> *</span></label>
    <div class="col-md-10">
        <textarea class="form-control" name="description" id="descripation" rows="4" placeholder="{{ __('static.testimonials.enter_description') }}" cols="50">{{ isset($testimonial->description) ? $testimonial->description : old('description') }}</textarea>
        @error('descripation')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="rating">{{ __('static.testimonials.rating') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="number" min="1" max="5" name="rating" id="rating"
            value="{{ isset($testimonial->rating) ? $testimonial->rating : old('rating') }}"
            placeholder="{{ __('static.testimonials.enter_rating') }}">
        @error('rating')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="image" class="col-md-2">{{ __('static.testimonials.image') }}</label>
    <div class="col-md-10">
        <input class='form-control' type="file" id="image" name="image">
        @error('image')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

</div>
@if (isset($testimonial) && isset($testimonial->media?->first()->original_url))
<div class="form-group">
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-10">
            <div class="image-list">
                <div class="image-list-detail">
                    <div class="position-relative">
                        <img src="{{ $testimonial->media?->first()->original_url }}"
                            id="{{ $testimonial->media?->first()->id }}" alt="User Image"
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

@push('js')
<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $("#testimonialForm").validate({
                ignore: [],
                rules: {
                    "name": "required",
                    "description": "required",
                    "rating": "required",
                },
            });
        });

    })(jQuery);
</script>
@endpush