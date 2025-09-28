    <div class="form-group row">
        <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
        <div class="col-md-10">
            <ul class="language-list">
                @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                    @if(isset($tag))
                        <li>
                            <a href="{{ route('backend.tag.edit', ['tag' => $tag->id, 'locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('backend.tag.index', ['locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @endif
                @empty
                    <li>
                        <a href="{{ route('backend.tag.edit', ['tag' => $tag->id, 'locale' => Session::get('locale', 'en')]) }}"
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
    <label class="col-md-2" for="name">{{ __('static.name') }} ({{ request('locale', app()->getLocale()) }})<span>
            *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" id="name" name="name"
            placeholder="{{ __('static.tag.enter_name') }} ({{ request('locale', app()->getLocale()) }})"
            value="{{ isset($tag->name) ? $tag->getTranslation('name', request('locale', app()->getLocale())) : old('name') }}">
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

<div class = "form-group row">
    <label for="address" class="col-md-2">{{ __('static.tag.description') }}
        ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <textarea class = "form-control" id="description"
            placeholder="{{ __('static.tag.enter_description') }} ({{ request('locale', app()->getLocale()) }})"
            rows="4" name="description" cols="50">{{ isset($tag) ? $tag->getTranslation('description', request('locale', app()->getLocale())) : old('description') }}</textarea>
        @error('description')
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

<input type="hidden" name="type" value="blog">

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($tag))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $tag->status ? 'checked' : '' }}>
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
            $("#tagForm").validate({
                ignore: [],
                rules: {
                    "name": "required"
                },
            });
        });

    })(jQuery);
</script>
@endpush
