<?php

namespace App\Providers;

use App\Events\CreateBidEvent;
use App\Events\CreateOtpEvent;
use App\Events\UpdateBidEvent;
use App\Events\VerifyProofEvent;
use App\Events\AssignBookingEvent;
use App\Events\CreateBookingEvent;
use App\Events\AddExtraChargeEvent;
use App\Events\CreateProviderEvent;
use App\Events\BookingReminderEvent;
use App\Events\CreateServicemanWithdrawRequestEvent;
use App\Listeners\CreateBidListener;
use App\Listeners\CreateOtpListener;
use App\Listeners\UpdateBidListener;
use App\Listeners\VerifyProofListener;
use App\Events\UpdateServiceProofEvent;
use App\Events\UpdateBookingStatusEvent;
use App\Listeners\AssignBookingListener;
use App\Listeners\CreateBookingListener;
use App\Events\CreateServiceRequestEvent;
use App\Listeners\AddExtraChargeListener;
use App\Listeners\CreateProviderListener;
use App\Events\CreateWithdrawRequestEvent;
use App\Events\UpdateServicemanWithdrawRequestEvent;
use App\Events\UpdateWithdrawRequestEvent;
use App\Listeners\BookingReminderListener;
use App\Events\CreateServiceEvent;
use App\Events\CreateUserEvent;
use App\Events\ZoomMeetingCreatedEvent;
use App\Listeners\CreateServiceListener;
use App\Listeners\CreateServicemanWithdrawRequestListener;
use App\Listeners\UpdateServiceProofListener;
use App\Listeners\UpdateBookingStatusListener;
use App\Listeners\CreateServiceRequestListener;
use App\Listeners\CreateUserListener;
use App\Listeners\CreateWithdrawRequestListener;
use App\Listeners\UpdateServicemanWithdrawRequestListener;
use App\Listeners\UpdateWithdrawRequestListener;
use App\Listeners\ZoomMeetingCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CreateProviderEvent::class => [
            CreateProviderListener::class,
        ],
        CreateBookingEvent::class => [
            CreateBookingListener::class,
        ],
        CreateWithdrawRequestEvent::class => [
            CreateWithdrawRequestListener::class,
        ],
        CreateServicemanWithdrawRequestEvent::class => [
            CreateServicemanWithdrawRequestListener::class,
        ],
        UpdateBookingStatusEvent::class => [
            UpdateBookingStatusListener::class,
        ],
        UpdateWithdrawRequestEvent::class => [
            UpdateWithdrawRequestListener::class,
        ],
        UpdateServicemanWithdrawRequestEvent::class => [
            UpdateServicemanWithdrawRequestListener::class,
        ],
        AddExtraChargeEvent::class => [
            AddExtraChargeListener::class,
        ],
        VerifyProofEvent::class => [
            VerifyProofListener::class,
        ],
        UpdateServiceProofEvent::class => [
            UpdateServiceProofListener::class,
        ],
        BookingReminderEvent::class => [
            BookingReminderListener::class,
        ],
        CreateServiceRequestEvent::class => [
            CreateServiceRequestListener::class,
        ],
        CreateBidEvent::class => [
            CreateBidListener::class,
        ],
        UpdateBidEvent::class => [
            UpdateBidListener::class,
        ],
        AssignBookingEvent::class => [
            AssignBookingListener::class,
        ],
        CreateOtpEvent::class => [
            CreateOtpListener::class,
        ],
        CreateServiceEvent::class => [
            CreateServiceListener::class,
        ],
        ZoomMeetingCreatedEvent::class => [
            ZoomMeetingCreatedListener::class
        ],
        CreateUserEvent::class => [
            CreateUserListener::class,
        ],
    ];
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {}

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
