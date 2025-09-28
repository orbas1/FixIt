<div class="form-group row">
    <label class="col-md-2" for="user_id">{{ __('static.provider-document.provider') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control user-dropdown" name="user_id"
            data-placeholder="{{ __('static.provider-document.select-provider') }}">
            <option class="select-placeholder" value=""></option>
            @foreach ($providers as $key => $provider)
                <option value="{{ $provider->id }}" sub-title="{{ $provider->email }}"
                    image="{{ $provider->getFirstMedia('image')?->getUrl() }}"
                    @if (isset($providerDocument)) @if ($provider->id == $providerDocument->user_id) selected @endif @endif>
                    {{ $provider->name }}
                </option>
            @endforeach
        </select>
        @error('user_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="document_id">{{ __('static.provider-document.document') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control " name="document_id"
            data-placeholder="{{ __('static.provider-document.select-document') }}">
            <option class="select-placeholder" value=""></option>
            @foreach ($documents as $key => $option)
                <option class="option" value="{{ $key }}"
                    @if (isset($providerDocument)) @if ($key == $providerDocument->document_id) selected @endif @endif>{{ $option }}</option>
            @endforeach
        </select>
        @error('document_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="identity_no">{{ __('static.provider-document.document_number') }}<span>
            *</span></label>
    <div class="col-md-10">
        <input class='form-control' type="text" name="identity_no" id="identity_no"
            value="{{ isset($providerDocument->identity_no) ? $providerDocument->identity_no : old('name') }}"
            placeholder="{{ __('static.provider-document.enter_document_number') }}">
        @error('identity_no')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2">{{ __('static.provider-document.image') }}<span> *</span>
    </label>
    <div class="col-md-10">
        <input class='form-control' type="file" accept=".jpg, .png, .jpeg" id="image" name="image">
        @error('image')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if (isset($providerDocument))
    <div class="form-group">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-10">
                <div class="image-list">
                    @foreach ($providerDocument->media as $media)
                        <div class="image-list-detail">
                            <div class="position-relative">
                                <img src="{{ $media['original_url'] }}" id="{{ $media['id'] }}" alt="User Image"
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
    <label class="col-md-2" for="status">{{ __('static.provider-document.status') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control" name="status" id="status"
            data-placeholder="{{ __('static.provider-document.select_status') }}">
            <option class="select-placeholder" value=""></option>
            @foreach (['pending' =>  __('static.provider-document.pending'), 'approved' => __('static.provider-document.approved'), 'rejected' => __('static.provider-document.rejected')] as $key => $option)
                <option class="option" value="{{ $key }}" @if (
                    (isset($providerDocument) && $key == $providerDocument->status) ||
                        (!isset($providerDocument) && $key == 'approved')) selected @endif>
                    {{ $option }}</option>
            @endforeach
        </select>
        @error('status')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
@push('js')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                $("#providerDocumentForm").validate({
                    ignore: [],
                    rules: {
                        "user_id": "required",
                        "document_id": "required",
                        "identity_no": "required",
                        "status": "required",
                        "image": {
                            required: isDocumentImages(),
                            accept: "image/jpeg, image/png"
                        }
                    },
                    messages: {
                        "image": {
                            accept: "Only JPEG and PNG files are allowed."
                        }
                    }
                });
            });

            function isDocumentImages() {
                @if (isset($providerDocument->media) && !$providerDocument->media->isEmpty())
                    return false;
                @else
                    return true;
                @endif
            }

        })(jQuery);
    </script>
@endpush
