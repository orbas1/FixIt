<?php

namespace App\Listeners;

use App\Events\UpdateServicemanWithdrawRequestEvent;
use App\Helpers\Helpers;
use App\Models\User;
use App\Notifications\UpdateServicemanWithdrawRequestNotification;
use Exception;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateServicemanWithdrawRequestListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(UpdateServicemanWithdrawRequestEvent $event): void
    {
        try {
            $serviceman = User::find($event->servicemanWithdrawRequest->serviceman_id);
            if (isset($serviceman)) {
                $this->sendPushNotification($serviceman->fcm_token, $event);
                $serviceman->notify(new UpdateServicemanWithdrawRequestNotification($event->servicemanWithdrawRequest));
                $sendTo = ('+'.$serviceman?->code.$serviceman?->phone);
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event));
            }
        } catch (Exception $e) {
            
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
                    '{{amount}}' => Helpers::getDefaultCurrencySymbol() . $event->servicemanWithdrawRequest->amount,
                    '{{status}}' => $event->servicemanWithdrawRequest->status,
                ];
                
                $title = $content->title[$locale];
                $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

            } else {
                $title = "Your withdrawal request for " . Helpers::getDefaultCurrencySymbol() . $event->servicemanWithdrawRequest->amount . " has been " . $event->servicemanWithdrawRequest->status . ".";
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
                        'serviceman_wallet' => (string) $event->servicemanWithdrawRequest->serviceman_wallet_id,
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
                '{{amount}}' => Helpers::getDefaultCurrencySymbol() . $event->servicemanWithdrawRequest->amount,
                '{{status}}' => $event->servicemanWithdrawRequest->status,
            ];
            
            $message = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

        } else {
            $message = "Your withdrawal request for " . Helpers::getDefaultCurrencySymbol() . $event->servicemanWithdrawRequest->amount . " has been " . $event->servicemanWithdrawRequest->status . ".";
        }
        return $message;
    }

}
