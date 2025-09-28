<?php

namespace App\Listeners;

use App\Enums\RoleEnum;
use App\Events\CreateServiceRequestEvent;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Models\User;
use App\Notifications\CreateServiceRequestNotification;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateServiceRequestListener 
{
    use InteractsWithQueue;

    public $queue = 'createServiceRequest';
    /**
     * Handle the event.
     */
    public function handle(CreateServiceRequestEvent $event)
    {
        $serviceRequest = $event->serviceRequest;
        $categoryIds = is_array($serviceRequest->category_ids) ? $serviceRequest->category_ids : json_decode($serviceRequest->category_ids, true);
        $zones = Category::whereIn('id', $categoryIds)->with('zones:id')->get()->pluck('zones.*.id')->flatten()->unique()->toArray();
        
        foreach ($zones as $zoneId) {
            $topic = "zone_{$zoneId}";
            $this->sendPushNotification($topic, $event); 
        }

        $admin = User::role(RoleEnum::ADMIN)->first();
        if ($admin) {
            $admin->notify(new CreateServiceRequestNotification($serviceRequest, RoleEnum::ADMIN));
        }

        $providers = User::role(RoleEnum::PROVIDER)->whereHas('zones', function ($q) use ($zones) {
                $q->whereIn('zone_id', $zones);
            })->get();
        
        foreach ($providers as $provider) {
            $sendTo = ('+'.$provider?->code.$provider?->phone);
            Helpers::sendSMS($sendTo, $this->getSMSMessage($event));   
            $provider->notify(new CreateServiceRequestNotification($event->serviceRequest, RoleEnum::PROVIDER));
        }
    }

    public function sendPushNotification($topic, $event)
    {
        if ($topic) {
            $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
            $slug = 'new-service-request-provider'; 

            $content = PushNotificationTemplate::where('slug', $slug)->first();
            
            if ($content) {
                $data = [
                    '{{service_request_title}}' => $event?->serviceRequest?->title,
                ];

                $title = $content?->title[$locale];
                $body = str_replace(array_keys($data), array_values($data), $content?->content[$locale]);
            } else {
                $title = "New Service Request Available!";
                $body = "A new service request has been created. Place your bid now.";
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
                        'service_request_id' => (string) $event?->serviceRequest?->id,
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
        $slug = 'new-service-request-provider'; 
        $content = SmsTemplate::where('slug', $slug)->first();
        
        if ($content) {
            $data = [
                '{{service_request_title}}' => $event?->serviceRequest?->title,
            ];

            $message = str_replace(array_keys($data), array_values($data), $content?->content[$locale]);
        } else {
            $message = "A new service request has been created. Place your bid now.";
        }
        return $message;
    }

}
