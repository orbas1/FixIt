<?php

namespace App\Listeners;

use Exception;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use App\Events\BookingReminderEvent;
use App\Notifications\BookingReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Illuminate\Queue\InteractsWithQueue;

class BookingReminderListener
{
    use InteractsWithQueue;

    public $queue = 'BookingReminderEvent';

    /**
     * Handle the event.
     */
    public function handle(BookingReminderEvent $event)
    {
        try {
            $consumer = $event->booking->consumer;
            if (isset($consumer) && is_null($event->booking->parent_id)) {
                $topic = 'user_' . $consumer->id;
                $this->createNotificationPayload($topic, $event, RoleEnum::CONSUMER);
                $consumer->notify(new BookingReminderNotification($event->booking, RoleEnum::CONSUMER));
                $sendTo = ('+'.$consumer?->code.$consumer?->phone);
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::CONSUMER));
            }

            foreach ($event->booking->sub_bookings as $sub_order) {
                if (isset($sub_order->provider_id)) {
                    $provider = Helpers::getProviderById($sub_order->provider_id);
                    $topic = 'user_' . $provider->id;
                    $this->createNotificationPayload($topic, $event, RoleEnum::PROVIDER);
                    $provider = $provider?->provider;
                    $provider->notify(new BookingReminderNotification($sub_order, RoleEnum::PROVIDER));
                    $sendTo = ('+'.$provider?->code.$provider?->phone);
                    Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::PROVIDER));
                }
            }

            $admin = User::role(RoleEnum::ADMIN)->first();
            if (isset($admin)) {
                $admin->notify(new BookingReminderNotification($event->booking, RoleEnum::ADMIN));
                $sendTo = ('+'.$admin?->code.$admin?->phone);
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::ADMIN));
            }

        } catch (Exception $e) {

            //
        }
    }

    public function getSMSMessage($role, $event)
    {
        $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();


        switch ($role) {
            case 'admin':
                $message = "A consumer has scheduled a new booking #{$event->booking->booking_number}. Please review the booking details.";
                $slug = 'booking-scheduled-admin';
                break;
            case 'provider':
                $message = "You have a booking scheduled for today. Booking #{$event->booking->booking_number}. Please be prepared.";
                $slug = 'booking-reminder-provider';
                break;
            case 'user':
                $message = "This is a reminder for your booking today. Booking #{$event->booking->booking_number}. Please be ready.";
                $slug = 'booking-reminder-consumer';
                break;
        }
        $content = SmsTemplate::where('slug', $slug)->first();
        if ($content) {
            $data = [
                '{{booking_number}}' => $event?->booking?->booking_number,
            ];
            
            $message = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
        }

        return $message;
    }

    protected function createNotificationPayload($token, $event, $role)
    {
        $title = '';
        $body = '';
        $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
    
        $slug = '';
        switch ($role) {
            case 'admin':
                $slug = 'booking-scheduled-admin';
                break;
            case 'provider':
                $slug = 'booking-reminder-provider';
                break;
            case 'user':
                $slug = 'booking-reminder-consumer';
                break;
        }
    
        $content = PushNotificationTemplate::where('slug', $slug)->first();
    
        if ($content) {
            $data = [
                '{{booking_number}}' => $event?->booking?->booking_number,
            ];
    
            $title = $content->title[$locale];
            $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
        } else {
            switch ($role) {
                case 'admin':
                    $title = 'New Booking Scheduled';
                    $body = "A consumer has scheduled a new booking #{$event->booking->booking_number}. Please review the booking details.";
                    break;
                case 'provider':
                    $title = 'Reminder: Upcoming Booking Today';
                    $body = "You have a booking scheduled for today. Booking #{$event->booking->booking_number}. Please be prepared.";
                    break;
                case 'user':
                    $title = 'Reminder: Your Booking Today';
                    $body = "This is a reminder for your booking today. Booking #{$event->booking->booking_number}. Please be ready.";
                    break;
            }
        }
    
        if ($token) {
            $notification = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'image' => '',
                    ],
                    'data' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'booking',
                        'booking_id' => (string) $event?->booking?->id,
                    ],
                ],
            ];
    
            Helpers::pushNotification($notification);
        }
    }
    
}
