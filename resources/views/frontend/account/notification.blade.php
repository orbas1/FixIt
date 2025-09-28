@use('app\Helpers\Helpers')
@extends('frontend.layout.master')
@section('title', 'Notifications')
@section('breadcrumb')
<nav class="breadcrumb breadcrumb-icon">
    <a class="breadcrumb-item" href="{{url('/')}}">{{ __('frontend::static.account.home') }}</a>
    <span class="breadcrumb-item active">{{ __('frontend::static.account.notifications') }}</span>
</nav>
@endsection
@section('content')
<!-- Service List Section Start -->
<section class="section-b-space">
    <div class="container-fluid-md">
        <div class="profile-body-wrapper">
            <div class="row">
                @includeIf('frontend.account.sidebar')
                <div class="col-xxl-9 col-xl-8">
                    <button
                        class="filter-btn btn theme-bg-color text-white w-max d-xl-none d-inline-block mb-3">{{ __('frontend::static.account.show_menu') }}</button>
                    <div class="profile-main h-100">
                        <div class="card m-0">
                            <div class="card-header">
                                <div class="title-3">
                                    <h3>{{ __('frontend::static.account.notifications') }}</h3>
                                </div>
                                <form action="{{ route('frontend.notifications.webMarkAsRead') }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    <a class="btn btn-link p-0 text-primary">
                                        <i class="iconsax" icon-name="task-list"></i>
                                        {{ __('frontend::static.account.mark_as_all_read') }}
                                    </a>
                                </form>
                            </div>
                            <div class="card-body">
                                <ul class="notifications no-data-notifications">
                                    @php
                                    $notifications = auth()->user()->notifications?->paginate();
                                    @endphp
                                    @forelse ($notifications as $notification)
                                    <li class="notification-list @if (!$notification->read_at) 'unread' @endif">
                                        <div class="notify">
                                            <div class="notify-icon">
                                                <i class="iconsax" icon-name="clock"></i>
                                            </div>
                                            <div class="notify-note">
                                                <div class=" d-flex align-content-center justify-between">
                                                    <h5>{{ __('frontend::static.account.reminder') }}</h5>
                                                    <div class="notify-time">
                                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                                <p> {{ $notification->data['message'] }}</p>
                                            </div>
                                        </div>
                                    </li>
                                    @empty
                                    <div class="no-data-found">
                                        {{-- <img class="img-fluid no-data-img"
                                            src="{{ asset('frontend/images/no-data.svg')}}" alt=""> --}}
                                            <svg class="no-data-img">
                                                <use xlink:href="{{ asset('frontend/images/no-data.svg#no-data')}}"></use>
                                            </svg>
                                        <p>{{ __('frontend::static.account.notifications_not_found') }}</p>
                                    </div>
                                    @endforelse
                                </ul>

                                @if(count($notifications ?? []))
                                @if($notifications?->lastPage() > 1)
                                <div class="pagination-main pt-0">
                                    <ul class="pagination-box">
                                        {!! $notifications?->links() !!}
                                    </ul>
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Service List Section End -->
@endsection

@push('js')
<script>
$(document).ready(function() {
    "use strict";

    setTimeout(function() {
        $.ajax({
            url: "{{ route('frontend.notifications.markAsRead') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('.notification-list.unread').each(function() {
                    $(this).removeClass(
                    'unread'); // Remove 'unread' class from the li
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }, 5000); // 5 seconds delay
});
</script>
@endpush