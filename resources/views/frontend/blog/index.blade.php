@use('app\Helpers\Helpers')
@extends('frontend.layout.master')
@section('title', __('frontend::static.blogs.blogs'))

@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}"> {{__('frontend::static.blogs.home')}}</a>
    <span class="breadcrumb-item active">{{__('frontend::static.blogs.blogs')}}</span>
</nav>
@endsection
@section('content')
<!-- Blog Section Start -->
<section class="blog-section section-b-space" id="blog">
    <div class="container-fluid-lg">
        <div class="blog-content">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-sm-4 g-3 ratio2_1">
                @forelse ($blogs as $blog)
                <div class="col ">
                    <div class="blog-main">
                        <div class="card">
                            <div class="overflow-hidden b-r-5">
                                <a href="{{ route('frontend.blog.details', $blog?->slug) }}" class="card-img">
                                    <img src="{{ $blog?->web_img_thumb_url }}" alt="{{ $blog?->title }}"
                                        class="bg-img">
                                </a>
                            </div>
                            <div class="card-body">
                                <h4>
                                    <a href="{{ route('frontend.blog.details', $blog?->slug) }}">{{ $blog?->title }}</a>
                                </h4>

                                <ul class="blog-detail">
                                    <li>{{ $blog?->categories?->first()?->title }}</li>
                                    <li> {{ Helpers::dateTimeFormat($blog?->created_at, 'd M, Y') }}</li>
                                </ul>
                                <div class="blog-footer">
                                    <div>
                                        <i class="iconsax" icon-name="message-dots"></i>
                                        <span>{{$blog?->comments_count}}</span>
                                    </div>
                                    <span>
                                        - {{__('frontend::static.blogs.by')}} {{$blog?->created_by?->name ?? 'unknown'}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="no-data-found">
                    <svg class="no-data-img">
                        <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                    </svg>
                    {{-- <img class="img-fluid no-data-img" src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                    <p>{{__('frontend::static.blogs.not_found')}} </p>
                </div>
                @endforelse
            </div>
        </div>
        @if(count($blogs ?? []))
        @if($blogs?->lastPage() > 1)
        <div class="pagination-main">
            <ul class="pagination">
                {!! $blogs->links() !!}
            </ul>
        </div>
        @endif
        @endif
    </div>
</section>
<!-- Blog Section End -->
@endsection