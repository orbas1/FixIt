<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateProviderNotification extends Notification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $provider;

    /**
     * Create a new notification instance.
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($notifiable->hasRole(RoleEnum::ADMIN)) {
            return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
        }
        else{
            
            $content = EmailTemplate::where('slug', 'new-provider-registered-user')->first();
            if (!$content) {
                return (new MailMessage)
                ->line('The introduction to the notification.')
                ->line('Thank you for using our application!');
            }

            $locale = request()->hasHeader('Accept-Lang') ? 
                      request()->header('Accept-Lang') : 
                      app()->getLocale();

            $data = [
                '{{user_name}}' => $notifiable->name, 
                '{{provider_name}}' => $this->provider->name, 
                '{{Your Company Name}}' => config('app.name'),
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
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $thumbnail = asset(env('APP_URL').'/admin/images/notification/'.'Icon.png');

        return [
            'title' => 'New Provider registered!',
            'message' => "Exciting News! A new provider, {$this->provider->name}, has joined our website. Discover their incredible services and deals today. Also, stay tuned for updates on recent check request approvals and rejections.",
            'provider_id' => "{$this->provider->id}",
            'type' => 'provider',
            'thumbnail' => $thumbnail,
        ];
    }
}
