@extends('backend.layouts.master')

@section('title', __('static.testimonials.create'))

@section('content')
    <div class="row">
        <div class="card tab2-card">
            <div class="card-header">
                <h5>{{ __('static.testimonials.create') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.testimonial.store') }}" id="testimonialForm" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @include('backend.testimonial.fields')
                    <div class="text-end">
                        <button id='submitBtn' type="submit"
                            class="btn btn-primary spinner-btn ms-auto">{{ __('static.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
