<?php

namespace App\Listeners;

use App\Events\AssignBookingEvent;
use App\Helpers\Helpers;
use App\Notifications\AssignBookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignBookingListener
{
    use InteractsWithQueue;
    
    public $queue = 'assignBooking';
    /**
     * Handle the event.
     */
    public function handle(AssignBookingEvent $event)
    {
        $serviceMen = $event->booking->servicemen()->get();
        if ($serviceMen) {
            foreach ($serviceMen as $serviceman) {
                $topic = 'user_' . $serviceman->id;
                $this->sendPushNotification($topic, $event, $serviceman);
                $serviceman->notify(new AssignBookingNotification($event->booking, $serviceMen));
            }
        }
    }

    public function sendPushNotification($topic, $event, $serviceman)
    {
        if ($topic) {
            $title = "Booking status is {$event->booking?->booking_status?->name}";
            $body = "Booking Number: #{$event->booking?->booking_number} has been {$event->booking?->booking_status?->name} to you.";

            $notification = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'image' => '',
                    ],
                    'data' => [
                        'booking_id' => (string) $event?->booking?->id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'booking',
                    ],
                ],
            ];

            Helpers::pushNotification($notification);
        }
    }
}
