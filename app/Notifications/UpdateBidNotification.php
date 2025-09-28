<?php

namespace App\Notifications;

use App\Enums\BidStatusEnum;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateBidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $serviceRequest;
    private $user;
    private $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceRequest $serviceRequest, User $user, string $status)
    {
        $this->serviceRequest = $serviceRequest;
        $this->user = $user;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $content = EmailTemplate::where('slug', 'update-bid-provider')->first();
        
        if (!$content) {
        return (new MailMessage)
            ->subject("Bid {$this->status} for Service Request: {$this->serviceRequest->title}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("The bid for the service request '{$this->serviceRequest->title}' has been {$this->status} by {$this->user->name}.");
            
        }
        $locale = request()->hasHeader('Accept-Lang') ? 
        request()->header('Accept-Lang') : 
        app()->getLocale();
        $data = [
            '{{notifiable_name}}' => $notifiable->name,
            '{{service_request_title}}' => $this->serviceRequest->title,
            '{{bid_status}}' => $this->status,
            '{{user_name}}' => $this->user->name,
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
     * Get the array representation of the notification for saving in the database.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'status' => $this->status,
            'message' => "The bid for the service request '{$this->serviceRequest->title}' has been {$this->status} by {$this->user->name}.",
        ];
    }
}