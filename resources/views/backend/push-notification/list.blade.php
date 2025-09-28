@extends('backend.layouts.master')
@section('title', __('static.notification.list_notifications'))
@section('content')
    <div class="row">
        <div class="m-auto col-xl-10 col-xxl-8">
            <div class="card tab2-card">
                <div class="card-header">
                    <h5>{{ __('static.notification.list_notifications') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="notification-setting">
                        @forelse (auth()->user()->notifications as $notification)
                            <li @if (!$notification->read_at) class="unread" @endif>
                                <h4>
                                    {{ $notification->data['message'] }}
                                </h4>
                                <h5>
                                    <i data-feather="clock"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </h5>
                            </li>
                        @empty
                            <div class="d-flex flex-column no-data-detail">
                                <img class="mx-auto d-flex" src="{{ asset('admin/images/svg/no-data.svg') }}"
                                    alt="no-image">
                                <div class="data-not-found">
                                    <span>Data Not Found</span>
                                </div>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            "use strict";

            setTimeout(function() {
                $.ajax({
                    url: "{{ route('backend.notifications.markAsRead') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {},
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }, 5000); // 5 seconds delay
        });
    </script>
@endpush
