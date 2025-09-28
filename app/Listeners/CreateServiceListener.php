<?php

namespace App\Listeners;

use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Events\CreateServiceEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\CreateProviderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateServiceListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'createService';
    /**
     * Handle the event.
     */
    public function handle(CreateServiceEvent $event)
    {
        try {
            $admin = User::role(RoleEnum::ADMIN)->first();
            $users_mail = User::role(RoleEnum::CONSUMER)->get();
            $users = User::whereNotNull('fcm_token')->role(RoleEnum::CONSUMER)->whereNotNull('fcm_token')->get();

            $users = User::whereNotNull('fcm_token')->role(RoleEnum::CONSUMER)->pluck('fcm_token')->all();

            foreach ($users as $token) {

                $notification = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $event->service->name.'The new service is listed',
                            'body' => '',
                            'image' => '',
                        ],
                        'data' => [
                            'service' => (string) $event->service->title,
                            'service_id' => (string) $event?->service?->id,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'type' => 'service',
                            'image' => $event->service->getFirstMediaUrl('image'),
                        ],
                    ],
                ];


                Helpers::pushNotification($notification);
            }

            if (isset($admin)) {
                $admin->notify(new CreateProviderNotification($event->service));
            }
            if (isset($users_mail)) {
                foreach ($users_mail as $user) {
                    $user->notify(new CreateProviderNotification($event->service));
                }
            }
        } catch (Exception $e) {
        }
    }
}
