<?php

namespace App\Listeners;

use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Events\CreateProviderEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\PushNotificationTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\CreateProviderNotification;

class CreateProviderListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'createProvider';
    /**
     * Handle the event.
     */
    public function handle(CreateProviderEvent $event)
    {
        try {
            $admin = User::role(RoleEnum::ADMIN)->first();
            $users_mail = User::role(RoleEnum::CONSUMER)->get();
            $topic = 'createProvider';
            $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
            $slug = 'new-provider-registered-user';

            $content = PushNotificationTemplate::where('slug', $slug)->first();
            if($topic) {
                $data = [
                    '{{provider_name}}' => $event->provider->name,
                ];

                if ($content) {
                    $title = $content->title[$locale];
                    $body = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
                } else {
                    $title = "{$event->provider->name} has registered as a new provider.";
                    $body = "Check out the new provider: {$event->provider->name}.";
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
                            'provider' => (string) $event->provider->name,
                            'provider_id' => (string) $event?->provider?->id,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'type' => 'provider',
                            'image' => $event->provider->getFirstMediaUrl('image'),
                        ],
                    ],
                ];

                Helpers::pushNotification($notification);
            }

            if (isset($admin)) {
                $admin->notify(new CreateProviderNotification($event->provider));
            }
            if (isset($users_mail)) {
                foreach ($users_mail as $user) {
                    $user->notify(new CreateProviderNotification($event->provider));
                }
            }
        } catch (Exception $e) {

        }
    }

}
