@use('App\Models\Setting')
@php
    $settings = Setting::pluck('values')?->first();
@endphp

<!-- footer start-->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 d-flex align-items-center justify-content-center justify-content-md-start">
                <div class="footer-copyright">
                    <p class="mb-0">{{ $settings['general']['copyright'] ?? '' }}</p>
                </div>
            </div>
            @if (env('APP_VERSION'))
                <div class="col-md-6">
                    <div class="app-version-box"
                        {{-- class="ms-auto me-md-0 me-auto mt-md-0 mt-3 gap-2 d-flex justify-content-sm-end justify-content-center" --}}
                        >
                        <span class="badge d-flex badge-version-primary">{{ __('static.version') }}:
                            {{ env('APP_VERSION') }}</span>
                        <span class="badge d-flex badge-version-primary">{{ __('static.load_time') }}:
                            {{ round(microtime(true) - LARAVEL_START, 2) }}s.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</footer>
<!-- footer end-->
