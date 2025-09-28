<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;

class CreateBidNotification extends Notification
{
    use Queueable;

    private $serviceRequest;

    private $provider;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceRequest $serviceRequest, User $provider)
    {
        $this->serviceRequest = $serviceRequest;
        $this->provider = $provider;
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
        $content = EmailTemplate::where('slug', 'new-bid-notification-consumer')->first();
        if (!$content) {
        return (new MailMessage)
                ->subject('New Bid on Your Service Request')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line("You've received a new bid on your service request '{$this->serviceRequest->title}' from {$this->provider->name}.")
                ->action('View Bid', url("/service-requests/{$this->serviceRequest->id}"))
                ->line('Thank you for using our platform!');
        }

        $locale = request()->hasHeader('Accept-Lang') ? 
                      request()->header('Accept-Lang') : 
                      app()->getLocale();
        $data = [
                '{{notifiable_name}}' => $notifiable->name,
                '{{service_request_title}}' => $this->serviceRequest->title,
                '{{provider_name}}' => $this->provider->name,
                '{{service_request_id}}' => $this->serviceRequest->id,
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
    
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'provider_name' => $this->provider->name,
            'message' => "You've received a new bid on your service request '{$this->serviceRequest->title}' from {$this->provider->name}.",
        ];
    }
}
