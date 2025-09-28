@use('app\Helpers\Helpers')
@php
    $languages = Helpers::getLanguages();
    $defaultLocale = app()?->getLocale();
    $settings = Helpers::getSettings();

@endphp
@extends('backend.layouts.master')

@section('title', $eventAndShortcodes['name'])

@section('content')
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ @$eventAndShortcodes['name'] }}</h5>
                    <div class="button-container p-0">
                        <div class="dropdown shortcode-dropdown-box">
                            <button type="button" class="btn dropdown-btn" data-bs-toggle="dropdown"><i
                                    class="ri-code-s-slash-line"></i>
                                Dropdown link</button>

                            <ul class="dropdown-menu">
                                @forelse ($eventAndShortcodes["shortcodes"] as $key => $shortcode)
                                    <li>
                                        <span class="dropdown-item add-shortcode"
                                            data-text="{{ $shortcode['action'] }}">{{ $shortcode['text'] }}</span>
                                    </li>
                                @empty
                                    <p>{{ __('static.push_notification_templates.no_buttons_found') }}</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
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
                            <p>{{ __('static.push_notification_templates.no_languages_found') }}</p>
                        @endforelse
                    </ul>

                    <form method="POST" id="pushNotificationTemplatesForm"
                        action="{{ route('backend.push-notification-template.update', @$slug) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="tab-content" id="accountContent">
                            @foreach ($languages as $key => $language)
                                <div class="tab-pane fade {{ session('active_tab') == $key ? 'show active' : '' }}"
                                    id="tab-{{ $language['locale'] }}" role="tabpanel"
                                    aria-labelledby="tab-{{ $language['locale'] }}-tab">
                                    <div class="row g-4 align-items-start">
                                        <div class="col-lg-7">
                                            <div class="push-notification">
                                                <div class="row g-4 align-items-center">
                                                    <div class="col-12">
                                                        <div class="form-group row">
                                                            <label class="col-md-2 mb-2" for="title">
                                                                {{ __('static.push_notification_templates.title') }}<span>*</span>
                                                            </label>
                                                            <div class="col-md-10">
                                                                <input class="form-control" type="text"
                                                                    id="title_{{ $language['locale'] }}"
                                                                    name="title[{{ $language['locale'] }}]"
                                                                    value="{{ @$content['title'][$language['locale']] }}"
                                                                    placeholder="{{ __('static.push_notification_templates.enter_title') }}">
                                                                @error("title.{$language['locale']}")
                                                                    <span class="invalid-feedback d-block" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <label class="col-md-2 mb-2" for="content">
                                                                {{ __('static.push_notification_templates.content') }}<span>*</span>
                                                            </label>
                                                            <div class="col-md-10">
                                                                <textarea class="form-control" placeholder="{{ __('static.push_notification_templates.enter_content') }}"
                                                                    rows="4" id="content_{{ $language['locale'] }}" name="content[{{ $language['locale'] }}]" cols="50">{{ @$content['content'][$language['locale']] }}</textarea>
                                                                @error("content.{$language['locale']}")
                                                                    <span class="invalid-feedback d-block" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-primary ms-auto"
                                                                    name="save">
                                                                    {{ __('static.push_notification_templates.save') }}
                                                                </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-5 text-center">
                                            <div class="notification-mobile-box">
                                                <div class="notify-main">
                                                    <img src="{{ asset('admin/images/notify.png') }}"
                                                        class="notify-img img-fluid">
                                                    <div class="notify-content">
                                                        <h2 class="current-time" id="current-time"></h2>
                                                        <div class="notify-data">
                                                            <div class="message mt-0">
                                                                <img id="notify-image"
                                                                    src="{{ asset($settings['general']['favicon']) }}"
                                                                    alt="user">
                                                                <div class="notifi-head">
                                                                    <h5>{{ config('app.name') }}</h5>
                                                                    <span>{{ __('static.sms_templates.3_min_ago') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="notifi-footer">
                                                                <h5 id="notify-title">
                                                                    {{ @$content['title'][$language['locale']] }}</h5>
                                                                <p id="notify-message">
                                                                    {{ @$content['content'][$language['locale']] }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
    <script type="text/javascript" src="{{ asset('admin/js/time.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            const defaultLocale = `<?php echo $defaultLocale; ?>`;
            $('#pushNotificationTemplatesForm').validate({
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

            $('.add-shortcode').on('click', function() {
                var text = $(this).data('text');

                var activeTab = $('.tab-pane.show.active');
                var languageLocale = activeTab.attr('id').split('-')[1];
                var textarea = $('#content_' + languageLocale);

                var start = textarea[0].selectionStart;
                var end = textarea[0].selectionEnd;

                textarea.val(textarea.val().substring(0, start) + text + textarea.val().substring(end));

                textarea[0].selectionStart = textarea[0].selectionEnd = start + text.length;

                textarea.focus();
            });

        })(jQuery)
    </script>
@endpush
