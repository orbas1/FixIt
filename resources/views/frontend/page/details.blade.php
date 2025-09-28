@use('app\Helpers\Helpers')
@extends('frontend.layout.master')
@section('title', @$page?->title )


@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{ url('/') }}">{{__('frontend::static.blogs.home')}}</a>
    <a class="breadcrumb-item" href="{{ route('frontend.blog.index') }}">{{__('frontend::static.blogs.blogs')}}</a>
    <span class="breadcrumb-item active">{{ $page->title }}</span>
</nav>
@endsection
@section('content')
@if ($page)
<!-- Blog Section Start -->
<section class="blog-section ratio_40">
    <div class="container-fluid-md">
        <div class="detail-content">
            <div class="title">
                <h4>{{ $page?->title }}</h4>
            </div>

            <div class="detail-sec">
                <ul class="overview-list">
                    {!! $page?->content !!}
                </ul>
            </div>
        </div>
    </div>
</section>
@endif
@endsection


