<?php

namespace App\Listeners;

use App\Enums\RoleEnum;
use App\Events\CreateWithdrawRequestEvent;
use App\Models\User;
use App\Notifications\CreateWithdrawRequestNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use App\Helpers\Helpers;
use Illuminate\Queue\InteractsWithQueue;

class CreateWithdrawRequestListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'createWithdrawReques';

    /**
     * Handle the event.
     */
    public function handle(CreateWithdrawRequestEvent $event)
    {
        try {
            $admin = User::role(RoleEnum::ADMIN)->first();
            if (isset($admin)) {
                $admin->notify(new CreateWithdrawRequestNotification($event->withdrawRequest));
                $sendTo = ('+'.$admin?->code.$admin?->phone);
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event));
            }

        } catch (Exception $e) {
            //
        }
    }
    private function getSMSMessage($event)
    {
        $locale = app()->getLocale();
        $slug = 'withdrawal-request-admin'; 
         
        $content = SmsTemplate::where('slug', $slug)->first();
        if ($content) {
            $data = [
                '{{amount}}' => Helpers::getDefaultCurrencySymbol() . $event->withdrawRequest->amount,      
            ];
            
            $message = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
        } else {
            $message = "A new withdrawal request of " . Helpers::getDefaultCurrencySymbol() . $event->withdrawRequest->amount . " has been created. Please review and process it.";
        }
        return $message;
    }
}
