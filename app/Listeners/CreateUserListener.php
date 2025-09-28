<?php

namespace App\Listeners;

use Exception;
use App\Events\CreateUserEvent;
use App\Helpers\Helpers;
use App\Notifications\CreateUserNotification;

class CreateUserListener
{
    /**
     * Handle the event.
     */
    public function handle(CreateUserEvent $event): void
    {
        try{
            $user = $event->user;
            $appName = config('app.name');

            $notification = [
                'message' => [
                    'topic' => 'register_user',
                    'notification' => [
                        'title' => "🎉 Welcome Aboard, {$user->name}!",
                        'body'  => "Hello {$user->name} 👋, We’re thrilled to have you join the {$appName} family 🎉. From hassle-free bookings to trusted professionals, everything is just a tap away ✨. Start exploring today 🚀",
                        'image' => '',
                    ],
                    'data' => [
                        'user' => (string) $user->name,
                        'user_id' => (string) $user->id,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'user',
                        'image' => $user->getFirstMediaUrl('image'),
                    ],
                ],
            ];

            Helpers::pushNotification($notification);

            // Send Email
            $user->notify(new CreateUserNotification($user));
            
        }  catch (Exception $e) {

        }    
    }
}
