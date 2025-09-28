@extends('frontend.layout.master')

@section('title', __('frontend::static.privacy.privacy_policy'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.wishlist.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.privacy.privacy_policy')}}</span>
</nav>
@endsection

@section('content')
<!-- Recent Privacy Policy Section Start -->
<section class="privacy-section section-b-space section-bg">
    <div class="container-fluid-lg">
        <div class="privacy-content">
            <div class="row">
            @if(count($themeOptions['privacy_policy']['banners'] ?? []))
            <div class="col-xxl-8 col-xl-9 col-lg-10 mx-auto">
                <div class="accordion" id="privacyPolicyExample">
                    @foreach ($themeOptions['privacy_policy']['banners'] ?? [] as $key => $banners)
                    <div class="accordion-item">
                        @isset($banners['title'])
                        <h2 class="accordion-header">
                            <button class="accordion-button {{$loop->first ? '':'collapsed'}}" type="button" data-bs-toggle="collapse"
                                data-bs-target="#privacyPolicyCollapseTwo{{ $key }}" aria-expanded="false"
                                aria-controls="privacyPolicyCollapseTwo">
                                {{ $banners['title'] }}
                                <i class="iconsax add" icon-name="add"></i>
                                <i class="iconsax minus" icon-name="minus"></i>
                            </button>
                        </h2>
                        @endisset
                        @isset($banners['description'])
                        <div id="privacyPolicyCollapseTwo{{ $key }}" class="accordion-collapse collapse {{$loop->first ? 'show': 'collapsed'}}"
                            data-bs-parent="#privacyPolicyExample">
                            <div class="accordion-body">
                                {!! $banners['description'] !!}
                            </div>
                        </div>
                        @endisset
                    </div>
                    
                    @endforeach
                </div>
            </div>
            @else
            <div class="col-12">
                <div class="no-data-found bg-white">
                    <svg class="no-data-img">
                        <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                    </svg>
                    <p>{{__('frontend::static.privacy.data_not_found')}}</p>
                </div>
            </div>
            @endif
                
            </div>
        </div>
    </div>
</section>
<!-- Recent Privacy Policy Section End -->
@endsection