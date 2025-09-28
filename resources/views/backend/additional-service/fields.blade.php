@use('app\Helpers\Helpers')

    <div class="form-group row">
        <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
        <div class="col-md-10">
            <ul class="language-list">

                @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                    @if(isset($additionalService))
                        <li>
                            <a href="{{ route('backend.additional-service.edit', ['additional_service' => $additionalService->id, 'locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('backend.additional-service.create', ['locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @endif
                @empty
                    <li>
                        <a href="{{ route('backend.additional-service.edit', ['additional_service' => $additionalService->id, 'locale' => Session::get('locale', 'en')]) }}"
                            class="language-switcher active" target="blank"><img
                                src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i
                                data-feather="arrow-up-right"></i></a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>


<input type="hidden" name="locale" value="{{ request('locale') }}">
<div class="form-group row">
    <label class="col-md-2" for="parent_id">{{ __('static.service.services') }}<span> *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control user-dropdown" id="parent_id" name="parent_id" data-placeholder="{{ __('static.additional_service.select_service') }}">
                <option class="select-placeholder" value=""></option>
            @foreach ($services as $key => $option)
                <option value="{{ $option->id }}" image="{{ $option->getFirstMedia('image')?->getUrl() }}"
                    @if (old('parent_id', isset($additionalService) ? $additionalService->parent_id : '') == $option->id) selected @endif>{{ $option->title }}</option>
            @endforeach
        </select>
        @error('parent_id')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="thumbnail" class="col-md-2">{{ __('static.categories.thumbnail') }}
        ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="file" id="thumbnail" name="thumbnail">
        @error('thumbnail')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if (isset($additionalService))
    @php
        $locale = request('locale');
        $mediaItems = $additionalService->getMedia('thumbnail')->filter(function ($media) use ($locale) {
            return $media->getCustomProperty('language') === $locale;
        });
    @endphp
    @if ($mediaItems->count() > 0)
        <div class="form-group">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-10">
                    <div class="image-list">
                        @foreach ($mediaItems as $media)
                            <div class="image-list-detail">
                                <div class="position-relative">
                                    <img src="{{ $media->getUrl() }}" id="{{ $media->id }}" alt="User Image"
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
@endif

<div class="form-group row">
    <label class="col-md-2" for="title">{{ __('static.title') }}
        ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class='form-control' type="text" id="title" name="title"
            value="{{ isset($additionalService->title) ? $additionalService->getTranslation('title', request('locale', app()->getLocale())) : old('title') }}"
            placeholder="{{ __('static.service.enter_title') }} ({{ request('locale', app()->getLocale()) }})">
        @error('title')
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

@hasrole('provider')
    <input type="hidden" name="provider_id" value="{{ auth()->user()->id }}" id="provider_id">
@endhasrole

<div class="form-group row">
    <label class="col-md-2" for="price">{{ __('static.service.price') }}<span> *</span></label>
    <div class="col-md-10 error-div">
        <div class="input-group mb-3 flex-nowrap">
            <span class="input-group-text">{{ Helpers::getSettings()['general']['default_currency']->symbol }}</span>
            <div class="w-100">
                <input class='form-control' type="number" id="price" name="price" min="1"
                    value="{{ isset($additionalService->price) ? $additionalService->price : old('price') }}"
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
    <label class="col-md-2" for="role">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($additionalService))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $additionalService->status ? 'checked' : '' }}>
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
                $("#additionalServiceForm").validate({
                    ignore: [],
                    rules: {
                        "thumbnail": {
                            required: isServiceImage,
                        },
                        "title": "required",
                        "price": "required",
                        "parent_id": {
                            required: true
                        },
                    }
                });
            });

            function isServiceImage() {
                @if (isset($additionalService->media) && !$additionalService->media->isEmpty())
                    return false;
                @else
                    return true;
                @endif
            }
        })(jQuery);
    </script>
@endpush
