@use('app\Helpers\Helpers')
@extends('frontend.layout.master')
@section('title', @$blog?->title )

@section('meta_description', @$blog?->meta_description ?? @$blog?->description)
@section('og_title', @$blog?->meta_title ?? @$blog?->title)
@section('og_description', @$blog->meta_description ?? @$blog?->description)
@section('og_image', @$blog?->media?->first()?->getUrl())
@section('twitter_title', @$blog?->meta_title ?? @$blog?->title)
@section('twitter_description', @$blog?->meta_description ?? @$blog?->description)
@section('twitter_image', @$blog?->media?->first()?->getUrl())


@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.blogs.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.blog.index') }}">{{__('frontend::static.blogs.blogs')}}</a>
    <span class="breadcrumb-item active">{{ $blog->title }}</span>
</nav>
@endsection
@section('content')
@if ($blog)
<!-- Blog Section Start -->
<section class="blog-section">
    <div class="container-fluid-md">
        <div class="blog-details-image">
            <img src="{{ $blog?->web_img_thumb_url }}" alt="{{ $blog?->title }}" class="img-fluid">
        </div>
        <div class="detail-content">
            <div class="title">
                <h4>{{ $blog?->title }}</h4>
                <span class="badge primary-light-badge d-sm-flex d-none">
                    {{ $blog?->tags?->first()?->name }}
                </span>
            </div>
            <div
                class="d-flex align-items-sm-center align-items-start gap-1 justify-content-between flex-sm-row flex-column">
                <ul class="blog-detail">
                    <li>{{ $blog?->categories?->first()->title }}</li>
                    <li>{{ Helpers::dateTimeFormat($blog?->created_at, 'd M, Y') }}</li>
                </ul>
                <span class="text-light">
                    - {{__('frontend::static.blogs.by')}} {{ $blog?->created_by?->name ?? 'unknown' }}
                </span>
            </div>

            <div class="detail-sec">
                <div class="details-title">
                    <h3>{{__('frontend::static.blogs.description')}}</h3>
                    <p>{{ $blog?->description }}</p>
                </div>

                <ul class="overview-list">
                    {!! $blog?->content !!}
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- Blog Section End -->

@if(count($blog?->comments ?? []))
<section class="review-section">
    <div class="container-fluid-md">
        <div class="title">
            <h3>{{__('frontend::static.blogs.comments')}} ({{ $blog->comments->where('parent_id', null)->count() }})
            </h3>
        </div>
        <ul class="review-content">
            @foreach($blog->comments as $comment)
            @if(is_null($comment->parent_id))
            @include('frontend.layout.comment', ['comment' => $comment])
            @endif
            @endforeach
        </ul>
    </div>
</section>
@endif

<!-- Create Comments Section Start -->
<section class="comment-section">
    <div class="container-fluid-md">
        <div class="title">
            <h3>{{__('frontend::static.blogs.leave_a_comment')}}</h3>
        </div>
        <form action="{{ route('frontend.comments.store', $blog->id) }}" class="" method="POST" id="commentForm">
            @csrf
            <div class="row g-md-4 g-sm-2 g-1">
                <div class="col-12">
                    <div class="form-group">
                        <label for="email">{{__('frontend::static.blogs.message')}}</label>
                        <i class="iconsax" icon-name="mail"></i>
                        <textarea class="form-control form-control-white" name="message" id="message"
                            placeholder="{{__('frontend::static.blogs.enter_message')}}"></textarea>
                        @error('message')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                @auth
                    <div class="col-12">
                        <button type="submit"
                            class="btn btn-solid mt-2">{{__('frontend::static.blogs.post_comment')}}</button>
                    </div>
                @endauth
                @guest
                    <div class="col-12">
                        <a href="{{ url('login') }}" class="btn btn-solid mt-2">{{__('frontend::static.blogs.post_comment')}}</a>
                    </div>
                @endguest
            </div>
        </form>
    </div>
</section>
<!-- Create Comments Section End -->
@endif

<!-- Recent Blog Section Start -->
<section class="blog-section section-b-space">
    <div class="container-fluid-md">
        <div class="title">
            <h2>{{__('frontend::static.blogs.recent_blogs')}}</h2>
            <a class="view-all" href="{{ route('frontend.blog.index') }}">
                {{__('frontend::static.home_page.view_all')}}
                <i class="iconsax" icon-name="arrow-right"></i>
            </a>
        </div>
        <div class="blog-content">
            <div class="row row-cols-1 row-cols-lg-3 g-3 ratio2_1 g-3">
                @forelse ($recentBlogs as $recentBlog)
                <div class="col">
                    <div class="blog-main">
                        <div class="card">
                            <div class="overflow-hidden b-r-5">
                                <a href="{{ route('frontend.blog.details', $recentBlog?->slug) }}" class="card-img">
                                    <img src="{{ $recentBlog?->web_img_thumb_url }}" alt="{{ $recentBlog?->title }}"
                                        class="bg-img">
                                </a>
                            </div>
                            <div class="card-body">
                                <h4>
                                    <a href="{{ route('frontend.blog.details', $recentBlog?->slug) }}">{{ $recentBlog?->title }}
                                    </a>
                                </h4>
                                <ul class="blog-detail">
                                    <li>{{ $recentBlog?->categories?->first()->title }}</li>
                                    <li> {{ Helpers::dateTimeFormat($recentBlog?->created_at, 'd M, Y') }}</li>
                                </ul>
                                <div class="blog-footer">
                                    <div>
                                        <i class="iconsax" icon-name="message-dots"></i>
                                        <span>{{ $recentBlog?->comments_count }}</span>
                                    </div>
                                    <span>
                                        - By {{ $recentBlog?->created_by?->name ?? 'unknown' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="no-data-found">
                    <p> {{__('frontend::static.blogs.not_found')}}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
<!-- Recent Blog Section End -->
@endsection

@push('js')
<script>
$(document).ready(function() {
    "use strict";

    $("#commentForm").validate({
        ignore: [],
        rules: {
            "message": "required",
        }
    });
});
</script>
@endpush