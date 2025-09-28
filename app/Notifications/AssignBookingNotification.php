<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignBookingNotification extends Notification
{
    use Queueable;

    protected $booking;
    protected $serviceMen;

    public function __construct(Booking $booking, $serviceMen)
    {
        $this->booking = $booking;
        $this->serviceMen = $serviceMen;
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
        $locale = request()->header('Accept-Lang') ?? app()->getLocale();
        $content = EmailTemplate::where('slug', 'booking-assigned-serviceman')->first();

        $data = [
            '{{serviceman_name}}' => $notifiable->name,          
            '{{booking_number}}' => $this->booking->booking_number, 
            '{{booking_status}}' => $this->booking->booking_status?->name ?? 'Assigned', 
            '{{service_name}}' => $this->booking->service?->title ?? 'Service',
            '{{company_name}}' => config('app.name'),  
            '{{date_time}}' => $this->booking->date_time ?? now()->format('Y-m-d H:i:s')
        ];

        if ($content) {
            $emailContent = str_replace(array_keys($data), array_values($data), $content->content[$locale] ?? $content->content['en']);
            $subject = $content->title[$locale] ?? $content->title['en'];
        } else {
            $subject = "Booking #{$this->booking->booking_number} Assigned";
            $emailContent = "Hello {$notifiable->name},\n\n"
                ."A booking (#{$this->booking->booking_number}) has been assigned to you "
                ."for service: {$this->booking->service?->title}.\n\n"
                ."Please check your dashboard for more details.";
        }
        
        // if (!$content) {
        //     return (new MailMessage)
        //     ->subject("Bid {$this->status} for Service Request: {$this->serviceRequest->title}")
        //     ->greeting('Hello ' . $notifiable->name . ',')
        //     ->line("The bid for the service request '{$this->serviceRequest->title}' has been {$this->status} by {$this->user->name}.");
        // }
        
        // $locale = request()->hasHeader('Accept-Lang') ? 
        // request()->header('Accept-Lang') : 
        // app()->getLocale();
        // $data = [
        //     '{{serviceman_name}}' => $notifiable->name,          
        //     '{{booking_number}}' => $this->booking->booking_number, 
        //     '{{booking_status}}' => $this->booking->booking_status->name, 
        //     '{{service_name}}' => $this->booking->service->title,
        //     '{{company_name}}' => config('app.name'),  
        //     '{{date_time}}' => $this->booking->date_time            
        // ];
    
        // $emailContent = str_replace(array_keys($data), array_values($data), $content->content[$locale]);
        
        return (new MailMessage) 
            ->subject($subject)
            ->markdown('emails.email-template', [
                'content' => $content ?? null,
                'emailContent' => nl2br($emailContent),
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
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'service_id' => $this->booking->service_id,
            'message' => "Booking #{$this->booking->booking_number} assigned",
        ];
    }
}