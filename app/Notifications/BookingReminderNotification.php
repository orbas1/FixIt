<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;

class BookingReminderNotification extends Notification
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
    public function toMail(object $notifiable)
    {
        switch ($this->roleName) {
            case RoleEnum::CONSUMER:
                return $this->toConsumerMail();
            case RoleEnum::PROVIDER:
                return $this->toProviderMail();
            case RoleEnum::ADMIN:
                return $this->toAdminMail();
        }
    }

    public function toAdminMail(): MailMessage
    {
        $content = EmailTemplate::where('slug', 'booking-scheduled-admin')->first();
    
        if (!$content) {
            return (new MailMessage)
                ->subject('New Booking Scheduled')
                ->line('A consumer has scheduled a new booking.')
                ->line('Booking Details:')
                ->line('Booking Number: '.$this->booking->booking_number)
                ->line('Consumer Name: '.$this->booking->consumer->name)
                ->line('Booking Date: '.$this->booking->booking_date->format('Y-m-d'))
                ->line('Please review the booking details.');
        }
    
        $locale = request()->hasHeader('Accept-Lang') ? 
            request()->header('Accept-Lang') : 
            app()->getLocale();
    
        $data = [
            '{{booking_number}}' => $this->booking->booking_number,
            '{{consumer_name}}' => $this->booking->consumer->name,
            '{{booking_date}}' => $this->booking->booking_date->format('Y-m-d'),
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
    

    public function toProviderMail(): MailMessage
    {
        $content = EmailTemplate::where('slug', 'booking-reminder-provider')->first();

        if (!$content) {
            return (new MailMessage)
                ->subject('Reminder: Upcoming Booking Today')
                ->line('This is a reminder that you have a booking scheduled for today.')
                ->line('Booking Details:')
                ->line('Booking Number: '.$this->booking->booking_number)
                ->line('Consumer Name: '.$this->booking->consumer->name)
                ->line('Booking Time: '.$this->booking->booking_time->format('H:i'))
                ->line('Please be prepared for the appointment.');
        }

        $locale = request()->hasHeader('Accept-Lang') ? 
            request()->header('Accept-Lang') : 
            app()->getLocale();

        $data = [
            '{{booking_number}}' => $this->booking->booking_number,
            '{{consumer_name}}' => $this->booking->consumer->name,
            '{{booking_time}}' => $this->booking->booking_time->format('H:i'),
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
        $content = EmailTemplate::where('slug', 'booking-reminder-consumer')->first();
    
        if (!$content) {
            return (new MailMessage)
                ->subject('Reminder: Your Booking Today')
                ->line('This is a reminder that you have a booking scheduled for today.')
                ->line('Booking Details:')
                ->line('Booking Number: '.$this->booking->booking_number)
                ->line('Provider Name: '.$this->booking->provider->name)
                ->line('Booking Time: '.$this->booking->booking_time->format('H:i'))
                ->line('Please be ready at the specified time.');
        }
   
        $locale = request()->hasHeader('Accept-Lang') ? 
            request()->header('Accept-Lang') : 
            app()->getLocale();
    
        $data = [
            '{{booking_number}}' => $this->booking->booking_number,
            '{{provider_name}}' => $this->booking->provider->name,
            '{{booking_time}}' => $this->booking->booking_time->format('H:i'),
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
            case RoleEnum::PROVIDER:
                $message = "Reminder: Upcoming Booking #{$this->booking->booking_number} Today.";
                $title = "You have a booking scheduled for today. Booking #{$this->booking->booking_number}. Please be prepared.";
                break;
            case RoleEnum::ADMIN:
                $message = "A consumer has scheduled a new booking #{$this->booking->booking_number}. Please review the booking details.";
                $title = "Today Scheduled Booking #{$this->booking->booking_number}";
                break;
            case RoleEnum::CONSUMER:
                $message = "This is a reminder for your booking today. Booking #{$this->booking->booking_number}. Please be ready.";
                $title = "Reminder: Your Booking #{$this->booking->booking_number} Today";
                break;
        }

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'order',
        ];
    }
}
