<?php

namespace App\Notifications;

use App\Enums\BookingEnum;
use App\Helpers\Helpers;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateBookingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $booking;

    private $consumer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, $consumer)
    {
        $this->booking = $booking;
        $this->consumer = $consumer;
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
    public function toMail(object $notifiable)
    {
        $settings = Helpers::getSettings();
        if ($this->booking->consumer_id) {
            $consumer = Helpers::getConsumerById($this->booking?->consumer_id);
            if ($consumer->name) {
                $content = EmailTemplate::where('slug', 'update-booking-status-consumer')->first();
                if (!$content) {
                    return (new MailMessage)
                    ->subject("Booking  ID: #{$this->booking?->booking_number} has been {$this->booking?->booking_status?->name}")
                    ->greeting("Hello {$consumer->name},")
                    ->line("We wanted to provide you with an update regarding your recent booking #{$this->booking?->booking_id}.")
                    ->line("Your booking status has been updated to {$this->booking?->booking_status?->name}. ")
                    ->line('Please feel free to reach out to us if you have any questions or need assistance.')
                    ->line('Thank you for choosing us for your service experience. We value your trust and support!');
                }
                $locale = request()->hasHeader('Accept-Lang') ? 
                request()->header('Accept-Lang') : 
                app()->getLocale();
                
                $data = [
                    '{{consumer_name}}' => $consumer?->name, 
                    '{{booking_number}}' => $this?->booking?->booking_number, 
                    '{{booking_status}}' => $this?->booking?->booking_status?->name, 
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
        }
    }

    public function getMessageStatus($status, $booking)
    {
        switch ($status) {
            case BookingEnum::PENDING:
                return "Your booking #{$booking?->booking_number} is pending. We will update you once it is confirmed.";
            case BookingEnum::ACCEPTED:
                return "Your booking #{$booking?->booking_number} has been accepted by the provider. Thank you for your patience.";
            case BookingEnum::ASSIGNED:
                return "Your booking #{$booking?->booking_number} has been assigned to a provider. You will receive further updates soon.";
            case BookingEnum::ON_THE_WAY:
                return "The provider for your booking #{$booking?->booking_number} is en route to your location.";
            case BookingEnum::ON_GOING:
                return "Your booking #{$booking?->booking_number} is ongoing. We will keep you informed of any updates.";
            // case BookingEnum::DECLINE:
            //     return "Unfortunately, your booking #{$booking?->booking_number} has been declined by the provider. Please contact support for assistance.";
            case BookingEnum::CANCEL:
                return "Your booking #{$booking?->booking_number} has been cancelled. If you have any questions, please contact support.";
            case BookingEnum::ON_HOLD:
                return "Your booking #{$booking?->booking_number} is on hold. We will update you as soon as possible.";
            case BookingEnum::START_AGAIN:
                return "Your booking #{$booking?->booking_number} has been restarted. We will notify you of any changes.";
            case BookingEnum::COMPLETED:
                return "Your booking #{$booking?->booking_number} has been successfully completed. Thank you for choosing our service.";
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // for consumer
        return [
            'title' => 'Booking status updated!',
            'message' => $this->getMessageStatus($this->booking?->booking_status?->name, $this->booking),
            'type' => 'booking',
            'booking_id' => $this->booking?->id,
        ];
    }
}
