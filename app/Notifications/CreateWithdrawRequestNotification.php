<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;

class CreateWithdrawRequestNotification extends Notification
{
    use Queueable;

    private $withdrawRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $provider = User::where('id', $this->withdrawRequest->provider_id)->pluck('name')->first();
        $admin = User::role(RoleEnum::ADMIN)->pluck('name')->first();

        $content = EmailTemplate::where('slug', 'withdrawal-request-provider')->first();

        if (!$content) {
            return (new MailMessage)
                ->subject("Withdrawal Request Submitted")
                ->greeting("Hello admin,")
                ->line("My withdrawal request has been submitted.")
                ->line("Requested Amount: {$this->withdrawRequest->amount}")
                ->line("Your Message:")
                ->line($this->withdrawRequest->message)
                ->line('Thank you for your attention');
        }

        $locale = request()->hasHeader('Accept-Lang') ? 
                  request()->header('Accept-Lang') : 
                  app()->getLocale();

        $data = [
            '{{user_name}}' => $provider,
            '{{requested_amount}}' => $this->withdrawRequest->amount,
            '{{user_message}}' => $this->withdrawRequest->message,
            '{{company_name}}' => config('app.name'),
        ];

        $emailContent = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

        return (new MailMessage)
            ->subject($content->title[$locale])
            ->markdown('emails.email-template', [
                'content' => $content,
                'emailContent' => $emailContent,
                'locale' => $locale
            ]);
    
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        //for admin
        $provider = User::where('id', $this->withdrawRequest->provider_id)->pluck('name')->first();
        $symbol = Helpers::getDefaultCurrencySymbol();

        return [
            'title' => 'New Withdraw Request',
            'message' => "A withdrawal request for {$symbol}{$this->withdrawRequest->amount} has been received from a {$provider}.",
            'type' => 'withdraw',
            'withdraw_id' => $this->withdrawRequest->id,
        ];
    }
}
