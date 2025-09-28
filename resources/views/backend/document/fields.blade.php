<div class="form-group row">
    <label class="col-md-2" for="title">{{ __('static.title') }}<span> *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" id="title" name="title"
            value="{{ isset($document->title) ? $document->title : old('title') }}"
            placeholder="{{ __('static.document.enter_document_name') }}">
        @error('title')
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
                @if (isset($document))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $document->status ? 'checked' : '' }}>
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
<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.document.is_required') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($document))
                    <input class="form-control" type="hidden" name="is_required" value="0">
                    <input class="form-check-input" type="checkbox" name="is_required" id="" value="1"
                        {{ $document->is_required ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="is_required" value="0">
                    <input class="form-check-input" type="checkbox" name="is_required" id="" value="1">
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
        $("#documentForm").validate({
            ignore: [],
            rules: {
                "title": "required",  
            }
        });
    });
})(jQuery);
</script>
@endpush