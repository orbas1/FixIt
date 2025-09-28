<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateServiceRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $serviceRequest;

    private $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($serviceRequest, $role)
    {
        $this->serviceRequest = $serviceRequest;
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($notifiable->hasRole(RoleEnum::ADMIN)) {
            $content = EmailTemplate::where('slug', 'new-service-request-admin')->first();

            if (!$content) {
                return (new MailMessage)
                    ->subject('New Service Request Submitted')
                    ->greeting('Hello!')
                    ->line('A new service request has been submitted and requires your review.')
                    ->line('Service Title: ' . $this->serviceRequest->title)
                    ->action('Review Service Request', url('/admin/service-requests/' . $this->serviceRequest->id))
                    ->line('Thank you for your prompt attention!');
            }
            $locale = request()->hasHeader('Accept-Lang') ? request()->header('Accept-Lang') : app()->getLocale();
            
            $data = [
                '{{service_title}}' => $this->serviceRequest->title,
                '{{service_description}}' => $this->serviceRequest->description,
                '{{service_price}}' => $this->serviceRequest->initial_price,
                '{{booking_date}}' => $this->serviceRequest->booking_date,
                '{{company_name}}' => config('app.name')
            ];

            $emailContent = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

            return (new MailMessage)
                ->subject($content->title[$locale])
                ->markdown('emails.email-template', [
                    'content' => $content,
                    'emailContent' => $emailContent,
                    'locale' => $locale
                ]);
        } else {
            $content = EmailTemplate::where('slug', 'new-service-request-provider')->first();

            if (!$content) {
                return (new MailMessage)
                    ->subject('New Service Request Created')
                    ->greeting('Hello!')
                    ->line('A new service request has been created and is available for bidding.')
                    ->line('Service Title: ' . $this->serviceRequest->title)
                    ->line('Description: ' . $this->serviceRequest->description)
                    ->line('Price: ' . $this->serviceRequest->initial_price)
                    ->line('Booking Date: ' . $this->serviceRequest->booking_date)
                    ->line('Thank you for using our application!');
            }

            $locale = request()->hasHeader('Accept-Lang') ? 
                        request()->header('Accept-Lang') : 
                        app()->getLocale();

            $data = [
                '{{service_title}}' => $this->serviceRequest->title,
                '{{service_description}}' => $this->serviceRequest->description,
                '{{price}}' => $this->serviceRequest->initial_price,
                '{{booking_date}}' => $this->serviceRequest->booking_date,
                '{{company_name}}' => config('app.name')
            ];

            $emailContent = str_replace(array_keys($data), array_values($data), $content->content[$locale]);

            return (new MailMessage)->subject($content->title[$locale])
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
        if($this->role === RoleEnum::ADMIN){
            return [
                'message' => 'A new service request has been submitted and requires your review.',
                'service_request_id' => $this->serviceRequest->id,
                'title' => $this->serviceRequest->title,
                'description' => $this->serviceRequest->description,
                'initial_price' => $this->serviceRequest->initial_price,
                'booking_date' => $this->serviceRequest->booking_date,
            ];
        } else {
            return [
                'message' => 'A new service request is available for bidding.',
                'title' => $this->serviceRequest->title,
                'description' => $this->serviceRequest->description,
                'initial_price' => $this->serviceRequest->initial_price,
                'booking_date' => $this->serviceRequest->booking_date
            ];
        }
    }
}
