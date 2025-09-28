@extends('backend.layouts.master')
@section('title', __('static.appearances.robots'))
@section('content')
    <div class="tax-create">
        <form id="Form" action="{{ route('backend.robot.update') }}" method="POST" enctype="multipart/form-data">
            <div class="row g-xl-4 g-3">
                @method('POST')
                @csrf
                <div class="col-xl-10 col-xxl-8 mx-auto">
                    <div class="card tab2-card">
                        <div class="card-header">
                            <h5>{{ __('static.appearances.edit_robot_file') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-md-2" for="content">{{ __('static.appearances.content') }}<span>
                                        *</span></label>
                                <div class="col-md-10">
                                    <textarea class="form-control" type="text" name="content" id="" value="" cols="30" rows="10"
                                        placeholder="{{ __('static.appearances.edit_robots') }}">{{ @$content }}</textarea>
                                    @error('content')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <span class="text-gray mt-1">
                                        {{ __('static.appearances.view_robot_file') }}
                                        <a href="{{ url('/robots.txt') }}" class="text-primary">
                                            <b>{{ __('static.here') }}</b>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div class="submit-btn">
                                <button type="submit" name="save" class="btn btn-primary spinner-btn ms-auto">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="row g-xl-4 g-3">
                    <div class="col-xl-10 col-xxl-8 mx-auto">
                        <div class="left-part">
                            <div class="contentbox">
                                <div class="inside">
                                    <div class="contentbox-title">
                                        <h3></h3>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-2" for="content">{{ __('static.appearances.content') }}<span>
                                                *</span></label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" type="text" name="content" id="" value="" cols="30" rows="10"
                                                placeholder="{{ __('static.appearances.edit_robots') }}">{{ @$content }}</textarea>
                                            @error('content')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <span class="text-gray mt-1">
                                                {{ __('static.appearances.view_robot_file') }}
                                                <a href="{{ url('/robots.txt') }}" class="text-primary">
                                                    <b>{{ __('static.here') }}</b>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-12">
                                            <div class="submit-btn">
                                                <button type="submit" name="save" class="btn btn-solid spinner-btn">
                                                    {{ __('Save') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function($) {
            "use strict";
            $('#Form').validate({
                rules: {
                    "content": "required",
                },
            });
        })(jQuery);
    </script>
@endpush
