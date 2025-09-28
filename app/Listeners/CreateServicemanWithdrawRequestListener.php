<?php

namespace App\Listeners;

use App\Enums\RoleEnum;
use App\Events\CreateServicemanWithdrawRequestEvent;
use App\Models\User;
use App\Notifications\CreateServicemanWithdrawRequestNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PushNotificationTemplate;
use App\Models\SmsTemplate;
use App\Helpers\Helpers;
use Illuminate\Queue\InteractsWithQueue;

class CreateServicemanWithdrawRequestListener
{
    use InteractsWithQueue;

    public $queue = 'createServicemanWithdraw';
    
    /**
     * Handle the event.
     */
    public function handle(CreateServicemanWithdrawRequestEvent $event)
    {
        try {
            $admin = User::role(RoleEnum::ADMIN)->first();
            if (isset($admin)) {
                $admin->notify(new CreateServicemanWithdrawRequestNotification($event->servicemanWithdrawRequest));
                $sendTo = ('+'.$admin?->code.$admin?->phone);
                Helpers::sendSMS($sendTo, $this->getSMSMessage($event));
            }

        } catch (Exception $e) {
          
        }
    }

    private function getSMSMessage($event)
    {
        $locale = app()->getLocale();
        
        $slug = 'withdrawal-request-admin'; 
        
        $content = SmsTemplate::where('slug', $slug)->first();
       
        if ($content) {
            $data = [
                '{{amount}}' => Helpers::getDefaultCurrencySymbol() . $event->servicemanWithdrawRequest->amount,      
            ];
            $message = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
          
        } else {
            $message = "A new withdrawal request of " . Helpers::getDefaultCurrencySymbol() . $event->servicemanWithdrawRequest->amount . " has been created. Please review and process it.";
        }
        return $message;
    }
}
