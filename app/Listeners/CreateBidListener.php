<?php

namespace App\Listeners;

use App\Models\User;
use App\Helpers\Helpers;
use App\Models\SmsTemplate;
use App\Events\CreateBidEvent;
use App\Models\ServiceRequest;
use App\Models\PushNotificationTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateBidListener
{
    use InteractsWithQueue;
    
    public $queue = 'createBid';
    /**
     * Handle the event.
     */
    public function handle(CreateBidEvent $event)
    {
        $serviceRequest = ServiceRequest::findOrFail($event->bid->service_request_id);
        $user = User::findOrFail($serviceRequest->user_id);
        $provider = User::findOrFail($event->bid->provider_id);
        if($user && $provider){
            $message = "You've received a new bid on your service request '{$serviceRequest->title}' from {$provider->name}.";
            $topic = 'user_' . $user->id;
            $this->sendPushNotification($topic, $message, $event);
            $sendTo = ('+'.$user?->code.$user?->phone);
            Helpers::sendSMS($sendTo, $this->getSMSMessage($event));
        }
    }

    public function sendPushNotification($topic, $message, $event)
    {
        if ($topic) {
            $slug = 'new-bid-notification-consumer';
            $content = PushNotificationTemplate::where('slug', $slug)->first();

            if ($content) {
                $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
                $data = [
                    '{{service_request_title}}' => $event->bid->serviceRequest->title,
                    '{{provider_name}}' => $event->bid->provider->name,
                ];

                $title = str_replace(array_keys($data), array_values($data), $content->title[$locale]);
                $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
            } else {
                $title = "New Service Request Available!";
                $body = $message;
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
                        'service_request_id' => (string) $event->bid->service_request_id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'service_request',
                    ],
                ],
            ];

            Helpers::pushNotification($notification);
        }
    }

    public function getSMSMessage($event)
    {
        $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
        $slug = 'new-bid-notification-consumer'; 
        $content = SmsTemplate::where('slug', $slug)->first();
        if ($content) {
            $data = [
                '{{service_request_title}}' => $event->bid->serviceRequest->title,
                '{{provider_name}}' => $event->bid->provider->name,
            ];
            
            $message = str_replace(array_keys($data), array_values($data), $content?->content[$locale]);
        } else {

            $message = "You've received a new bid on your service request '{$event->bid->serviceRequest->title}' from {$event->bid->provider->name}.";
        }
        return $message;
    }

}