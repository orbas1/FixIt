@isset($themeOptions['footer'])
    <!-- Footer Section Start -->
    <footer class="footer-section nav-folderized">
        <div class="container-fluid-lg">
            @if ($themeOptions['footer']['footer_copyright'] ?? false)
                <div class="copyright">
                    <a class="footer-logo" href="{{ url('/') }}" target="_blank">
                        <img src="{{ asset($themeOptions['general']['footer_logo'] ?? '') }}" alt="">
                    </a>
                    <p>{{ $themeOptions['footer']['footer_copyright'] }}</p>
                </div>
            @endif
            <div class="row gy-3 gy-md-4 gy-xl-0">
                <div class="col-xl-3 col-sm-6">
                    <div class="nav d-block">
                        <h3 class="heading-footer">
                            <svg>
                                <use xlink:href="{{ asset('frontend/images/footer-line.svg#footer-line') }}"></use>
                            </svg>
                            {{-- <img src="{{ asset('frontend/images/footer-line.svg') }}" alt=""> --}}
                            {{ __('frontend::static.footer.links') }}
                            <i class="iconsax d-sm-none d-inline-block down-arrow" icon-name="chevron-down"></i>
                        </h3>
                        <ul>
                            @if ($themeOptions['footer']['useful_link'] ?? false)
                                @forelse ($themeOptions['footer']['useful_link'] as $link)
                                    <li><a class="nav-link" href="{{ url($link['slug']) }}">{{ $link['name'] }} </a></li>
                                @empty
                                    <li><a class="nav-link"
                                            href="javascript:void(0)">{{ __('frontend::static.footer.no_link_found') }}</a>
                                    </li>
                                @endforelse
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="nav d-block">
                        <h3 class="heading-footer">
                            <svg>
                               <use xlink:href="{{ asset('frontend/images/footer-line.svg#footer-line') }}"></use>
                            </svg>
                            {{-- <img src="{{ asset('frontend/images/footer-line.svg') }}" alt=""> --}}
                            {{ __('frontend::static.footer.policy') }}
                            <i class="iconsax d-sm-none d-inline-block down-arrow" icon-name="chevron-down"></i>
                        </h3>
                        <ul>
                            @if ($themeOptions['footer']['pages'] ?? false)
                                @forelse ($themeOptions['footer']['pages'] as $page)
                                    <li><a class="nav-link" href="{{ url($page['slug']) }}">{{ $page['name'] }} </a>
                                    </li>
                                @empty
                                    <li><a class="nav-link"
                                            href="javascript:void(0)">{{ __('frontend::static.footer.pages_not_found') }}</a>
                                    </li>
                                @endforelse
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="nav d-block">
                        <h3 class="heading-footer">
                            <svg>
                               <use xlink:href="{{ asset('frontend/images/footer-line.svg#footer-line') }}"></use>
                            </svg>
                            {{-- <img src="{{ asset('frontend/images/footer-line.svg') }}" alt=""> --}}
                            {{ __('frontend::static.footer.other') }}
                            <i class="iconsax d-sm-none d-inline-block down-arrow" icon-name="chevron-down"></i>
                        </h3>
                        <ul>
                            @if ($themeOptions['footer']['others'] ?? false)
                                @forelse ($themeOptions['footer']['others'] as $other)
                                    @if ($other['slug'] == 'booking' || $other['slug'] == 'wishlist' || $other['slug'] == 'account/profile')
                                        @auth
                                            <li><a class="nav-link" href="{{ url($other['slug']) }}">{{ $other['name'] }}
                                                </a></li>
                                        @endauth
                                    @else
                                        <li><a class="nav-link" href="{{ url($other['slug']) }}">{{ $other['name'] }}
                                            </a></li>
                                    @endif
                                @empty
                                    <li><a class="nav-link"
                                            href="javascript:void(0)">{{ __('frontend::static.footer.others_not_found') }}</a>
                                    </li>
                                @endforelse
                            @endif
                        </ul>
                    </div>
                </div>

                @if ($themeOptions['footer']['become_a_provider']['become_a_provider_enable'] ?? false)
                    <div class="col-xl-3 col-sm-6">
                        <div class="nav d-block">
                            <h3 class="heading-footer">
                                <svg>
                                   <use xlink:href="{{ asset('frontend/images/footer-line.svg#footer-line') }}"></use>
                                </svg>
                                {{-- <img src="{{ asset('frontend/images/footer-line.svg') }}" alt=""> --}}
                                {{ __('frontend::static.footer.become_provider') }}
                                {{-- <i class="iconsax d-sm-none d-inline-block down-arrow" icon-name="chevron-down"></i> --}}
                            </h3>
                            <ul class="d-block">
                                <li>{{ $themeOptions['footer']['become_a_provider']['description'] }}</li>
                            </ul>
                            <a href="{{ url('backend/become-provider') }}"
                                class="btn btn-solid">{{ __('frontend::static.footer.register_now') }}
                                <i class="iconsax" icon-name="arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->
@endisset
