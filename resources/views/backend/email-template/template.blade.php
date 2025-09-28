@use('app\Helpers\Helpers')
@php
$languages = Helpers::getLanguages();
$defaultLocale = app()?->getLocale();
@endphp
@extends('backend.layouts.master')

@section('title', $eventAndShortcodes['name'])


@section('content')
<div class="row g-sm-4 g-3">
    <div class="col-12">
        <div class="card tab2-card">
            <div class="card-header">
                <h5>{{ @$eventAndShortcodes['name'] }}</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-material" id="account" role="tablist">
                    @forelse ($languages as $language)
                    <li class="nav-item">
                        <a class="nav-link @if ($loop->first) active show @endif"
                            id="tab-{{ $language['locale'] }}-tab" data-bs-toggle="tab"
                            href="#tab-{{ $language['locale'] }}" role="tab"
                            aria-controls="tab-{{ $language['locale'] }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            <img src="{{ asset($language['flag']) }}">
                            {{ $language['name'] }}
                            <i class="ri-error-warning-line danger errorIcon"></i>
                        </a>
                    </li>
                    @empty
                    <p>{{ __('static.email_templates.no_languages_found') }}</p>
                    @endforelse
                </ul>

                <form method="POST" id="emailTemplatesForm"
                    action="{{ route('backend.email-template.update', @$slug) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="tab-content" id="accountContent">
                        @foreach ($languages as $key => $language)
                        <div class="tab-pane fade {{ session('active_tab') == $key ? 'show active' : '' }}"
                            id="tab-{{ $language['locale'] }}" role="tabpanel"
                            aria-labelledby="tab-{{ $language['locale'] }}-tab">
                            <div class="row g-4 align-items-start">
                                <div class="col-12 col-md-7">
                                    <div class="push-notification">
                                        <div class="row g-4 align-items-center">
                                            <div class="col-12">
                                                <div class="form-group row">
                                                    <label class="col-md-2 mb-2" for="title">
                                                        {{ __('static.email_templates.title') }}<span>*</span>
                                                    </label>
                                                    <div class="col-md-10">
                                                        <input class="form-control" type="text"
                                                            id="title_{{ $language['locale'] }}"
                                                            name="title[{{ $language['locale'] }}]"
                                                            value="{{ @$content['title'][$language['locale']] }}"
                                                            placeholder="{{ __('static.email_templates.enter_title') }}">
                                                        @error("title.{$language['locale']}")
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-md-2 mb-2" for="content">
                                                        {{ __('static.email_templates.content') }}<span>*</span>
                                                    </label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control email-content" placeholder="{{ __('static.email_templates.enter_content') }}"
                                                            rows="4" id="content_{{ $language['locale'] }}" name="content[{{ $language['locale'] }}]" cols="50">{{ @$content['content'][$language['locale']] }}</textarea>
                                                        @error("content.{$language['locale']}")
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-12">
                                                        <button type="submit" class="btn btn-primary"
                                                            name="save">
                                                            {{ __('static.email_templates.save') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-5">
                                    <div class="card email-template-box">
                                        <div class="card-body">
                                            <h5 class="card-title" id="email-notify-title">{{ @$content['title'][$language['locale']] }}</h5>
                                            {!! @$content['content'][$language['locale']] !!}

                                        </div>
                                        <div class="card-footer text-muted text-center">&copy; 2024 Your Company Name. All rights reserved.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    (function($) {
        "use strict";
        const defaultLocale = `<?php echo $defaultLocale; ?>`;
        $('#emailTemplatesForm').validate({
            ignore: [],
            rules: {
                [`title[${defaultLocale}]`]: "required",
                [`content[${defaultLocale}]`]: "required",

            },
            invalidHandler: function(event, validator) {
                const $tabLink = $(`#tab-${defaultLocale}-tab`);
                $tabLink.find(".errorIcon").show();
                $(".nav-link.active").removeClass("active");
                $(".tab-pane.show").removeClass("show active");
                $(`#tab-${defaultLocale}`).addClass("show active");
                $tabLink.addClass("active");
            },
            success: function(label, element) {
                const $tabLink = $(`#tab-${defaultLocale}-tab`);
                const $invalidFields = $(`#tab-${defaultLocale}`).find(".error:visible");
                if ($invalidFields.length === 0) {
                    $tabLink.find(".errorIcon").hide();
                }
            }
        });

        tinymce.init({
            selector: '.email-content',
            setup: function(editor) {
                editor.on('init change', function() {
                    editor.save();
                });
            },
            plugins: [
                'shortcodes',
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table paste"
            ],
            toolbar: [
                "insertfile undo redo | styleselect | bold italic underline strikethrough | formatselect | shortcodes | forecolor backcolor code table | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
            ],
            image_title: true,
            file_picker_types: 'image',
            relative_urls: false,
            remove_script_host: false,
            images_upload_handler: function(blobInfo, success, failure) {
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                var $csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: "/admin/media/upload",
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $csrfToken
                    },
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.location) {
                            success(response.location);
                        } else {
                            failure('Invalid JSON response');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        failure('Image upload failed: ' + textStatus + ' - ' +
                            errorThrown);
                    }
                });
            },
            menubar: false,
            branding: false,
            placeholder: 'Enter your text here...',
        });
        var shortcodes = @json($eventAndShortcodes['shortcodes']);

        tinymce.PluginManager.add('shortcodes', function(editor, url) {
            var toggleState = false;
            editor.ui.registry.addMenuButton('shortcodes', {
                icon: 'sourcecode',
                text: 'Shortcodes',
                fetch: function(callback) {
                    var items = shortcodes.map(function(shortcode) {
                        return {
                            type: 'menuitem',
                            text: shortcode.text,
                            onAction: function() {
                                editor.insertContent('&nbsp;<p>' + shortcode
                                    .action + '</p>');
                            }
                        };
                    });
                    callback(items);
                }
            });
        });

    })(jQuery)
</script>
@endpush
