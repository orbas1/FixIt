@extends('backend.layouts.master')

@section('title', __('static.testimonials.edit'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.testimonials.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.testimonial.update', $testimonial->id) }}" id="testimonialForm"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @includeIf('backend.testimonial.fields')
                        <div class="text-end">
                            <button class="btn btn-primary spinner-btn ms-auto"
                                type="submit">{{ __('static.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
