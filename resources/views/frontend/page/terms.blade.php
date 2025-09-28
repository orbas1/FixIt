@extends('frontend.layout.master')

@section('title', __('frontend::static.terms.terms_conditions'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.terms.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.terms.terms_conditions')}}</span>
</nav>
@endsection

@section('content')

<!-- Recent Terms & Conditions Section Start -->
<section class="terms-section section-b-space section-bg">
    <div class="container-fluid-lg">
        <div class="terms-content">
            <div class="row">
            @if(count($themeOptions['terms_and_conditions']['banners'] ?? []))
                <div class="col-xxl-8 col-xl-9 col-lg-10 mx-auto">
                    <div class="accordion" id="privacyPolicyExample">
                        
                        @foreach ($themeOptions['terms_and_conditions']['banners'] ?? [] as $key => $banners)
                        <div class="accordion-item">
                            @isset( $banners['title'])
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#privacyPolicyCollapse{{ $key }}" aria-expanded="false"
                                    aria-controls="privacyPolicyCollapse{{ $key }}">
                                    {{ $banners['title'] }}
                                    <i class="iconsax add" icon-name="add"></i>
                                    <i class="iconsax minus" icon-name="minus"></i>
                                </button>
                            </h2>
                            @endisset
                            @isset( $banners['description'])
                            <div id="privacyPolicyCollapse{{ $key }}" class="accordion-collapse collapse"
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
                                <p>{{__('frontend::static.terms.data_not_found')}}</p>
                            </div>
                        </div>
                        @endif
            </div>
        </div>
    </div>
</section>
<!-- Recent Terms & Conditions Section End -->

@endsection