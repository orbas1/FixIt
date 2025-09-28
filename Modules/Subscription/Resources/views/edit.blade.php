@extends('backend.layouts.master')

@section('title', __('static.plan.edit'))

@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.plan.edit') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.plan.update', $plan->id) }}" id="planForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @includeIf('subscription::fields')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
