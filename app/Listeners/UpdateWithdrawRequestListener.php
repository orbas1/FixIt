<?php

namespace App\Listeners;

use App\Events\UpdateWithdrawRequestEvent;
use App\Helpers\Helpers;
use App\Models\User;
use App\Notifications\UpdateWithdrawRequestNotification;
use Exception;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateWithdrawRequestListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(UpdateWithdrawRequestEvent $event): void
    {
        try {
            $provider = User::find($event->withdrawRequest->provider_id);
            if (isset($provider)) {
                $this->sendPushNotification($provider->fcm_token, $event);
                $provider->notify(new UpdateWithdrawRequestNotification($event->withdrawRequest));
                $sendTo = ('+'.$provider?->code.$provider?->phone);
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event));
            }
        } catch (Exception $e) {
            // Handle exception
        }
    }
    public function sendPushNotification($token, $event)
    {
        
        if ($token) {
            $title = '';
            $body = '';
            $locale = app()->getLocale();
            $slug = 'update-withdraw-request-user';
            
            $content = PushNotificationTemplate::where('slug', $slug)->first();
            
            if ($content) {
                $data = [
                    '{{amount}}' => Helpers::getDefaultCurrencySymbol() . $event->withdrawRequest->amount,
                    '{{status}}' => $event->withdrawRequest->status,
                ];
                
                $title = $content->title[$locale];
                $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

            } else {
                $title = "Your withdrawal request for " . Helpers::getDefaultCurrencySymbol() . $event->withdrawRequest->amount . " has been " . $event->withdrawRequest->status . ".";
                $body = 'If you require any further assistance, please don’t hesitate to contact us.';
            }
        
            $notification = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'image' => '',
                    ],
                    'data' => [
                        'provider_wallet' => (string) $event->withdrawRequest->provider_wallet_id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'wallet',
                    ],
                ],
            ];

            Helpers::pushNotification($notification);
        }
    }

    public function getSMSMessage($event)
    {
        $locale =  app()->getLocale();
        $slug = 'update-withdraw-request-user'; 
        $content = SmsTemplate::where('slug', $slug)->first();
        if ($content) {
            $data = [
                '{{amount}}' => Helpers::getDefaultCurrencySymbol() . $event->withdrawRequest->amount,
                '{{status}}' => $event->withdrawRequest->status,
            ];
            
            $message = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

        } else {
            $message = "Your withdrawal request for " . Helpers::getDefaultCurrencySymbol() . $event->withdrawRequest->amount . " has been " . $event->withdrawRequest->status . ".";
        }
        return $message;
    }

}
