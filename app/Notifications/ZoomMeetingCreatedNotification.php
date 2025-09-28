<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ZoomMeetingCreatedNotification extends Notification implements ShouldQueue
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
        $joinUrl = $this->booking->videoConsultation?->join_url;
        return match ($this->roleName) {
            RoleEnum::CONSUMER => (new MailMessage)
                ->subject("Zoom Meeting Link for Booking #{$this->booking->booking_number}")
                ->greeting("Hello {$this->booking->consumer->name},")
                ->line("Your remote booking now has a Zoom meeting link.")
                ->action('Join Meeting', $joinUrl)
                ->line("Thank you for using " . config('app.name') . "."),

            RoleEnum::PROVIDER => (new MailMessage)
                ->subject("Zoom Meeting Created for Booking #{$this->booking->booking_number}")
                ->greeting("Hello {$notifiable->name},")
                ->line("A Zoom meeting has been scheduled for your booking.")
                ->action('View Booking', url('/provider/bookings/' . $this->booking->id))
                ->line("Please ensure you are ready on time."),

            RoleEnum::ADMIN => (new MailMessage)
                ->subject("Zoom Meeting Created for Booking #{$this->booking->booking_number}")
                ->line("A Zoom meeting has been created.")
                ->line("Booking Number: {$this->booking->booking_number}")
                ->line("Join URL: {$joinUrl}"),
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Zoom Meeting Created',
            'message' => "Zoom meeting created for booking #{$this->booking->booking_number}.",
            'type' => 'zoom_meeting',
            'booking_id' => $this->booking->id,
        ];
    }
}
