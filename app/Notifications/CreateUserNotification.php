<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreateUserNotification extends Notification
{
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $appName = config('app.name'); 
        return (new MailMessage)
            ->subject("🎉 Welcome Aboard, {$this->user->name}!")
            ->greeting("Hello {$this->user->name} 👋")
            ->line("We’re thrilled to have you join the {$appName} family 🎉.")
            ->line("From hassle-free bookings to trusted professionals, everything is just a tap away ✨.")
            ->line("Start exploring today 🚀")
            ->salutation("– The {$appName} Team");
    }

    public function toArray($notifiable)
    {
        $appName = config('app.name'); 

        return [
            'title' => "🎉 Welcome Aboard, {$this->user->name}!",
            'body'  => "Hello {$this->user->name} 👋, Welcome to {$appName} 🎉. Let’s get started 🚀",
        ];
    }
}
