<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use App\Models\booking;
use Illuminate\Bus\Queueable;
use App\Models\EmailTemplate;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreateBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $booking;

    private $roleName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, $roleName)
    {
        $this->booking = $booking;
        $this->roleName = $roleName;
        $this->queue = 'createBookingEvent';
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
        switch ($this->roleName) {
            case RoleEnum::CONSUMER:
                return $this->toConsumerMail();
            case RoleEnum::PROVIDER:
                return $this->toProviderMail($notifiable);
            case RoleEnum::ADMIN:
                return $this->toAdminMail();
        }
    }

    public function toAdminMail(): MailMessage
    {
        $content = EmailTemplate::where('slug', 'booking-created-admin')->first();
    
        if (!$content) {
            return (new MailMessage)
                ->subject("The booking #{$this->booking->booking_id} has been placed")
                ->line('A booking has been placed successfully.')
                ->line('Booking Payment Status: '.$this->booking->payment_status)
                ->line('Booking Status: '.$this->booking->booking_status->name)
                ->line('Your prompt attention is requested.');
        }
    
        $locale = request()->hasHeader('Accept-Lang') ? 
            request()->header('Accept-Lang') : 
            app()->getLocale();
    
        $data = [
            '{{booking_id}}' => $this->booking->booking_id,
            '{{payment_status}}' => $this->booking->payment_status,
            '{{booking_status}}' => $this->booking->booking_status->name,
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
    

    public function toProviderMail($notifiable): MailMessage
    {
        $content = EmailTemplate::where('slug', 'booking-created-provider')->first();
        if (!$content) {
            return (new MailMessage)
            ->subject("New booking #{$this->booking->booking_number} from Your Services")
            ->line('Congratulations! A new booking has been received from your Services.')
            ->line('booking Payment Status: '.$this->booking->payment_status)
            ->line('booking Status: '.$this->booking->booking_status->name)
            ->line('Thank you for partnering with us!')
            ->line('If you have any questions, please contact us.');
        }
        
        $locale = request()->hasHeader('Accept-Lang') ? 
        request()->header('Accept-Lang') : 
        app()->getLocale();
        $data = [
            '{{provider_name}}' => $notifiable->name, 
            '{{booking_number}}' => $this->booking->booking_number,
            '{{payment_status}}' => $this->booking->payment_status,
            '{{booking_status}}' => $this->booking->booking_status->name,
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

    public function toConsumerMail(): MailMessage
    {
        $content = EmailTemplate::where('slug', 'booking-created-consumer')->first();

        if (!$content) {
            return (new MailMessage)
                ->subject("Your booking #{$this->booking->booking_number} Confirmation")
                ->greeting("Hello {$this->booking->consumer->name},")
                ->line("We're excited to confirm your booking #{$this->booking->booking_number}.")
                ->line("Booking Payment Status: {$this->booking->payment_status}")
                ->line("Booking Status: {$this->booking->booking_status->name}")
                ->line('Thank you for choosing us for your service needs.');
        }

        $locale = request()->hasHeader('Accept-Lang') ? 
            request()->header('Accept-Lang') : 
            app()->getLocale();

        $data = [
            '{{booking_number}}' => $this->booking->booking_number,
            '{{consumer_name}}' => $this->booking->consumer->name,
            '{{payment_status}}' => $this->booking->payment_status,
            '{{booking_status}}' => $this->booking->booking_status->name,
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
        switch ($this->roleName) {
            case RoleEnum::CONSUMER:
                $message = "Your booking has been successfully placed. Booking #{$this->booking->booking_number}. Thank you for choosing us.";
                break;
            case RoleEnum::PROVIDER:
                $message = "A consumer has booking from your services list. Booking #{$this->booking->booking_number}. Please ensure prompt fulfillment.";
                break;
            case RoleEnum::ADMIN:
                $message = "The booking has been placed successfully. Booking #{$this->booking->booking_number}. Your prompt attention is requested.";
                break;
        }

        return [
            'title' => 'Booking has been placed',
            'message' => $message,
            'type' => 'booking',
            'booking_id' => $this->booking->id,
        ];
    }
}
