<?php

namespace App\Listeners;

use Exception;
use App\Helpers\Helpers;
use App\Models\SmsTemplate;
use App\Events\UpdateBookingStatusEvent;
use App\Models\PushNotificationTemplate;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\UpdateBookingStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateBookingStatusListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'updateBookingStatusEvent';
    /**
     * Handle the event.
     */
    public function handle(UpdateBookingStatusEvent $event)
    {
        try {
            if ($event->booking->consumer_id) {
                $consumer = Helpers::getConsumerById($event->booking->consumer_id);
                if ($consumer) {
                    $topic = 'user_' . $consumer->id;
                    $this->sendPushNotification($topic, $event);
                    $consumer->notify(new UpdateBookingStatusNotification($event->booking, $consumer));
                    $sendTo = ('+'.$consumer?->code.$consumer?->phone);
                    Helpers::sendSMS($sendTo, $this->getSMSMessage($event));
                }
            }

        } catch (Exception $e) {

            //
        }
    }

    public function sendPushNotification($topic, $event)
    {
        if ($topic) {
            $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();

            $slug = 'update-booking-status-consumer';

            $content = PushNotificationTemplate::where('slug', $slug)->first();
            $title = '';
            $body = '';

            if ($content) {
                $data = [
                    '{{booking_number}}' => $event->booking?->booking_number,
                    '{{status}}' => $event->booking?->booking_status?->name,
                ];

                $title = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
                $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
            } else {
                $title = "Booking status is {$event->booking?->booking_status?->name}";
                $body = "Booking Number: #{$event->booking?->booking_number} has been {$event->booking?->booking_status?->name}";
            }

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

    public function getSMSMessage($event)
    {
        $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
        $slug = 'update-booking-status-consumer';

        $content = SmsTemplate::where('slug', $slug)->first();
        if ($content) {
            $data = [
                '{{booking_number}}' => $event->booking?->booking_number,
                '{{status}}' => $event->booking?->booking_status?->name,
            ];
            $message = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
        }  else {
            $message = "Booking Number: #{$event->booking?->booking_number} has been {$event->booking?->booking_status?->name}";
        }

        return $message;
    }
}
