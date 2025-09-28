<?php

namespace App\Listeners;

use App\Enums\RoleEnum;
use App\Events\VerifyProofEvent;
use App\Helpers\Helpers;
use App\Models\User;
use App\Notifications\VerifyProofNotification;
use Exception;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyProofListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'addServiceProofEvent';

    /**
     * Handle the event.
     */
    public function handle(VerifyProofEvent $event)
    {
        try {
            $booking = $event->booking;
            if (!$booking || !$booking->provider_id) {
                return;
            }
            $provider = Helpers::getProviderById($booking->provider_id);
             if ($provider) {
                $topic = 'user_' . $provider->id;

                // Push notification to provider topic
                $this->sendPushNotification($topic, $event, RoleEnum::PROVIDER);

                // Laravel notification
                $provider->notify(new VerifyProofNotification($booking, RoleEnum::PROVIDER));

                // SMS
                $sendTo = '+' . $provider?->code . $provider?->phone;
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::PROVIDER));
            }

            /**
             * 🔹 Notify Admin
             */
            $admin = User::role(RoleEnum::ADMIN)->first();
            if ($admin) {
                $admin->notify(new VerifyProofNotification($booking, RoleEnum::ADMIN));

                $sendTo = '+' . $admin?->code . $admin?->phone;
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::ADMIN));
            }
            // if ($booking) {
            //     foreach ($booking->sub_bookings as $sub_booking) {
            //         if (isset($sub_booking->provider_id)) {
            //             $provider = Helpers::getProviderById($sub_booking->provider_id);                       
            //             if ($provider) {
            //                 $topic = 'user_' . $provider->id;
            //                 $this->sendPushNotification($topic, $event, RoleEnum::PROVIDER);
            //                 $provider->notify(new VerifyProofNotification($event->booking, RoleEnum::PROVIDER));
            //                 $sendTo = ('+'.$provider?->code.$provider?->phone);
            //                 Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::PROVIDER));
            //             }
            //         }
            //     }

            //     if ($booking->provider_id) {
            //         $provider = Helpers::getProviderById($sub_booking->provider_id); 
            //         $topic = 'user_' . $provider->id;
            //         $this->sendPushNotification($topic, $event, RoleEnum::PROVIDER);
            //         $provider->notify(new VerifyProofNotification($event->booking, RoleEnum::PROVIDER));
            //         $sendTo = ('+'.$provider?->code.$provider?->phone);
            //         Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::PROVIDER));
            //     }
                
            //     $admin = User::role(RoleEnum::ADMIN)->first();
            //     if ($admin) {
            //         $admin->notify(new VerifyProofNotification($event->booking, RoleEnum::ADMIN));
            //         $sendTo = ('+'.$admin?->code.$admin?->phone);
            //         Helpers::sendSMS($sendTo, $this->getSMSMessage($event, RoleEnum::PROVIDER));
            //     }

            // }
        } catch (Exception $e) {
            // Handle exception (e.g., log it)
        }
    }

    public function sendPushNotification($topic, $event, $role)
    {
        if ($topic) {
            $title = '';
            $body = '';
            $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
            
            switch ($role) {
                case RoleEnum::ADMIN:
                    $slug = 'proof-mail-admin';
                    break;
                case RoleEnum::PROVIDER:
                    $slug = 'proof-mail-provider';
                    break;
            }

            $content = PushNotificationTemplate::where('slug', $slug)->first();

            if ($content) {
                $data = [
                    '{{booking_number}}' => $event->booking->booking_number,
                ];

                $title = $content->title[$locale];
                $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

            } else {
                $title = "Service Proof Added for Booking #{$event->booking->booking_number}";
                $body = 'Your prompt attention is requested to verify the provided proof.';
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
                        'booking_id' => (string) $event->booking->id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'booking',
                    ],
                ],
            ];

            Helpers::pushNotification($notification);
        }
    }

    public function getSMSMessage($event, $role)
    {
        $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
        $slug = ''; 
        switch ($role) {
            case 'admin':
                $slug = 'proof-mail-admin';
                break;
            case 'provider':
                $slug = 'proof-mail-provider';
                break;
        }
    
        $content = SmsTemplate::where('slug', $slug)->first();
        if ($content) {
            $data = [
                '{{booking_number}}' => $event->booking?->booking_number,
            ];
            $message = str_replace(array_keys($data), array_values($data), $content?->content[$locale]);
        }  else {
            $message = "Service Proof Added for Booking #{$event->booking->booking_number}, Your prompt attention is requested to verify the provided proof.";
        }
        return $message;
    }
}
