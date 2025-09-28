<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;

class VerifyProofNotification extends Notification
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
    public function toMail(object $notifiable): MailMessage
    {
        switch ($this->roleName) {
            case RoleEnum::PROVIDER:
                return $this->toProviderMail();
            case RoleEnum::ADMIN:
                return $this->toAdminMail();
        }
    }

    public function toAdminMail(): MailMessage
    {
        $content = EmailTemplate::where('slug', 'service-proof-added-admin')->first();
    
        if (!$content) {
            return (new MailMessage)
                ->subject("Service Proof Added for Booking #{$this->booking->booking_number}")
                ->line("Service proof has been added for booking #{$this->booking->booking_number}.")
                ->line('Please review the provided proof at your earliest convenience.')
                ->line('If you have any questions, please contact us.');
        }
    
        $locale = request()->hasHeader('Accept-Lang') ? 
                  request()->header('Accept-Lang') : 
                  app()->getLocale();
    
        $data = [
            '{{booking_number}}' => $this->booking->booking_number,
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
        $content = EmailTemplate::where('slug', 'service-proof-added-provider')->first();
    
        if (!$content) {
            return (new MailMessage)
                ->subject("Service Proof Added for Booking #{$this->booking->booking_number}")
                ->line("Service proof has been added for booking #{$this->booking->booking_number}.")
                ->line('Your prompt attention is requested to verify the provided proof.');
        }
    
        $locale = request()->hasHeader('Accept-Lang') ? 
                  request()->header('Accept-Lang') : 
                  app()->getLocale();
    
        $data = [
            '{{booking_number}}' => $this->booking->booking_number,
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
        $booking = Helpers::getBookingById($this->booking?->id);
        switch ($this->roleName) {
            case RoleEnum::PROVIDER:
                $message = "Service Proof Added for Booking #{$this->booking->booking_number}. Please review.";
                break;
            case RoleEnum::ADMIN:
                $message = "Service Proof Added for Booking #{$this->booking->booking_number}. Your prompt attention is requested.";
                break;
        }

        return [
            'title' => 'Service Proof Added',
            'message' => "Service proof has been added for booking #{$this->booking->booking_number}. Your prompt attention is requested.",
            'booking_id' => $this->booking?->id,
            'type' => 'booking',
        ];
    }
}
