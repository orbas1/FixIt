<?php

namespace App\Listeners;

use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Events\ZoomMeetingCreatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ZoomMeetingCreatedNotification;

class ZoomMeetingCreatedListener
{
    use InteractsWithQueue;

    public $queue = 'zoomMeetingCreatedEvent';
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ZoomMeetingCreatedEvent $event): void
    {
        try {
            $booking = $event->booking;

            if ($booking->consumer) {
                $topic = 'user_' . $booking->consumer->id;
                $this->sendPushNotification($topic, $booking, RoleEnum::CONSUMER);
                $booking->consumer->notify(new ZoomMeetingCreatedNotification($booking, RoleEnum::CONSUMER));
            }

            if ($booking->provider_id) {
                $provider = Helpers::getProviderById($booking->provider_id);
                $topic = 'user_' . $provider->id;
                $this->sendPushNotification($topic, $booking, RoleEnum::PROVIDER);
                $provider->notify(new ZoomMeetingCreatedNotification($booking, RoleEnum::PROVIDER));
                Helpers::sendSMS('+' . $provider?->code . $provider?->phone, $this->getSMSMessage($booking, RoleEnum::PROVIDER));
            }

            $admin = User::role(RoleEnum::ADMIN)->first();
            if ($admin) {
                $admin->notify(new ZoomMeetingCreatedNotification($booking, RoleEnum::ADMIN));
                Helpers::sendSMS('+' . $admin?->code . $admin?->phone, $this->getSMSMessage($booking, RoleEnum::ADMIN));
            }

        } catch (Exception $e) {
            // Log error silently
        }
    }

    private function sendPushNotification($topic, $booking, $role)
    {
        $title = "Zoom Meeting Created";
        $body = match ($role) {
            RoleEnum::CONSUMER => "Your remote booking #{$booking->booking_number} now has a meeting link.",
            RoleEnum::PROVIDER => "A Zoom meeting has been created for booking #{$booking->booking_number}.",
            RoleEnum::ADMIN    => "Zoom meeting created for booking #{$booking->booking_number}.",
        };

        if ($topic) {
            $notification = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'image' => "",
                    ],
                    'data' => [
                        'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                        'type' => 'booking',
                        'booking_id' => (string) $booking->id,
                    ],
                ],
            ];
            Helpers::pushNotification($notification);
        }
    }

    private function getSMSMessage($booking, $role)
    {
        return match ($role) {
            RoleEnum::CONSUMER => "Your Zoom meeting link for booking #{$booking->booking_number} is ready.",
            RoleEnum::PROVIDER => "Zoom meeting created for booking #{$booking->booking_number}.",
            RoleEnum::ADMIN    => "Zoom meeting created for booking #{$booking->booking_number}.",
        };
    }
}
