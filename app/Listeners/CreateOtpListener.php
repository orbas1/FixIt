<?php

namespace App\Listeners;

use App\Models\User;
use App\Helpers\Helpers;
use App\Events\CreateOtpEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateOtpListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreateOtpEvent $event): void
    {
        $user = User::where('phone', $event->phone)->first();
        
        $otp = rand(100000, 999999); 
        
        $message = "Your OTP code is: {$otp}";
        $sendTo = '+' . $user?->code . $user?->phone;
        Helpers::sendSMS($sendTo, $message);
        DB::table('password_resets')->insert([
            'phone' => $event->phone,
            'otp' => $otp,
            'created_at' => now(),
        ]);
        if ($user) {
            $this->sendPushNotification($user->phone, "Your OTP has been sent to your mobile.");
        }
    }

    public function sendPushNotification($token, $message)
    {
        if ($token) {
            $notification = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => 'OTP Notification',
                        'body' => $message,
                    ],
                ],
            ];

            Helpers::pushNotification($notification);
        }
    }
}
