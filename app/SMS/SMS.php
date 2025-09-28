<?php

namespace App\SMS;

use App\Http\Traits\MessageTrait;
use Illuminate\Notifications\Notification;

class SMS
{
    use MessageTrait;

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return void
     */

    public function send($notifiable, Notification  $notification)
    {
        return $notification->toSend($notifiable);
    }

    public function sendSMS($message)
    {
        return $this->sendMessage($message);
    }
   
}