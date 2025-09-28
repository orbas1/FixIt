<div class="accordion-item" id="banner-{{ $index }}">
    <h2 class="accordion-header" id="heading-{{ $index }}">
        <button class="accordion-button  @if($index != 0) 'collapsed' @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" aria-expanded="@if($index != 0) 'true' @else 'false' @endif">{{ $banner['title'] ?? 'Privacy Policy' }}</button>
    </h2>
    @if ($banner == null)
    <div id="collapse-{{ $index }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ $index }}">
    @else
    <div id="collapse-{{ $index }}" class="accordion-collapse collapse @if($index == 0) 'show' @endif" aria-labelledby="heading-{{ $index }}">
    @endif
        <div class="accordion-body">
            <div class="form-group row">
                <label class="col-md-2">{{ __('Title') }}</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="privacy_policy[banners][{{ $index }}][title]" value="{{ $banner['title'] ?? '' }}" placeholder="Enter banner title">
                </div>
            </div>
            <div class="form-group row">
                <label for="breadcrumb_description" class="col-md-2">{{ __('Description') }}</label>
                <div class="col-md-10">
                    <textarea class="form-control summary-ckeditor" name="privacy_policy[banners][{{ $index }}][description]" id="banner_description___INDEX__" placeholder="{{ __('static.theme_options.enter_description') }}" rows="2">{{ $banner['description'] ?? '' }}</textarea>
                </div>
            </div>
            <button type="button" class="btn btn-danger remove-banner">Remove</button>
        </div>
    </div>
</div>
