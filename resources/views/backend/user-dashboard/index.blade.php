{{-- @use('App\Enums\RoleEnum')
@use('App\Helpers\Helpers')

@php
    $role = Helpers::getRoleByUserId(request()->id);
@endphp

<div class="card-body">

    <div class="card-header pt-0 px-0">
        @if ($role == RoleEnum::PROVIDER)
            <h5>{{ __('static.user_dashboard.provider_details') }}</h5>
        @elseif($role == RoleEnum::SERVICEMAN)
            <h5>{{ __('static.user_dashboard.serviceman_details') }}</h5>
        @elseif($role == RoleEnum::CONSUMER)
            <h5>{{ __('static.user_dashboard.customer_details') }}</h5>
        @endif
    </div>

    <div class="provider-details-box user-details-box">
        <img src="{{ asset('admin/images/svg/left.svg') }}" class="img-fluid left-image" alt="">
        <img src="{{ asset('admin/images/svg/right.svg') }}" class="img-fluid right-image" alt="">

        <div class="customer-image">
            @php
                $media = $user?->getFirstMedia('image');
                $imageUrl = $media ? $media->getUrl() : null;
            @endphp
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="Image" class="img-fluid">
            @else
                <div class="initial-letter">{{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>
        <div class="customer-name">
            <h3>
                <!-- <i class="iconsax" icon-name="user-1"></i> -->
                <!-- <span>{{ __('static.user_dashboard.provider_name') }} :</span> -->
                {{ $user->name }}
            </h3>
        </div>
        <ul class="list-unstyled">
            <li>
                <p>
                    <i class="iconsax" icon-name="mail"></i>
                    <span>{{ __('static.user_dashboard.email') }} :</span>
                    {{ $user->email }}
                </p>
            </li>
            <li>
                <p>
                    <i class="iconsax" icon-name="phone"></i>
                    <span>{{ __('static.user_dashboard.phone') }} :</span>
                    +{{ $user->code }} {{ $user->phone }}
                </p>
            </li>
        </ul>
    </div>
</div>
<div class="card-body pb-0">
    <div class="button-container">
        @if ($role == RoleEnum::PROVIDER)
            <a href="{{ route('backend.provider.general-info', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/provider/' . request()->id . '/general') ? 'active' : '' }}">
                {{ __('static.user_dashboard.general_info') }}
            </a>

            <a href="{{ route('backend.provider.get-bookings', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/provider/' . request()->id . '/bookings') ? 'active' : '' }}">
                {{ __('static.user_dashboard.bookings') }}
            </a>

            <a href="{{ route('backend.provider.get-servicemen', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/provider/' . request()->id . '/servicemen') ? 'active' : '' }}">
                {{ __('static.user_dashboard.servicemen') }}
            </a>

            <a href="{{ route('backend.provider.get-documents', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/provider/' . request()->id . '/documents') ? 'active' : '' }}">
                {{ __('static.user_dashboard.documents_list') }}
            </a>

            <a href="{{ route('backend.provider.get-reviews', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/provider/' . request()->id . '/reviews') ? 'active' : '' }}">
                {{ __('static.user_dashboard.reviews') }}
            </a>

            <a href="{{ route('backend.provider.get-withdraw-requests', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/provider/' . request()->id . '/withdraw-requests') ? 'active' : '' }}">
                {{ __('static.user_dashboard.withdraw_request') }}
            </a>
        @elseif ($role == RoleEnum::SERVICEMAN)
            <a href="{{ route('backend.servicemen.general-info', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/servicemen/' . request()->id . '/general') ? 'active' : '' }}">
                {{ __('static.user_dashboard.general_info') }}
            </a>

            <a href="{{ route('backend.servicemen.get-bookings', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/servicemen/' . request()->id . '/bookings') ? 'active' : '' }}">
                {{ __('static.user_dashboard.bookings') }}
            </a>

            <a href="{{ route('backend.servicemen.get-reviews', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/servicemen/' . request()->id . '/reviews') ? 'active' : '' }}">
                {{ __('static.user_dashboard.reviews') }}
            </a>

            <a href="{{ route('backend.servicemen.get-withdraw-requests', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/servicemen/' . request()->id . '/withdraw-requests') ? 'active' : '' }}">
                {{ __('static.user_dashboard.withdraw_request') }}
            </a>
        @elseif($role == RoleEnum::CONSUMER)
            <a href="{{ route('backend.consumer.general-info', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/consumer/' . request()->id . '/general') ? 'active' : '' }}">
                {{ __('static.user_dashboard.general_info') }}
            </a>

            <a href="{{ route('backend.consumer.get-bookings', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/consumer/' . request()->id . '/bookings') ? 'active' : '' }}">
                {{ __('static.user_dashboard.bookings') }}
            </a>

            <a href="{{ route('backend.consumer.get-reviews', request()->id) }}"
                class="btn shortcode-button {{ Request::is('backend/consumer/' . request()->id . '/reviews') ? 'active' : '' }}">
                {{ __('static.user_dashboard.reviews') }}
            </a>
        @endif
    </div>
</div> --}}
