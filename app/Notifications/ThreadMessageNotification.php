<?php

namespace App\Notifications;

use App\Helpers\Helpers;
use App\Models\ThreadMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ThreadMessageNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ThreadMessage $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $thread = $this->message->thread;
        $payload = [
            'thread_id' => $thread->public_id,
            'message_id' => $this->message->public_id,
            'body' => $this->message->body,
            'attachments' => $this->message->attachments,
            'author' => [
                'id' => $this->message->author->id,
                'name' => $this->message->author->name,
            ],
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];

        if ($notifiable->fcm_token) {
            $this->sendPushNotification($notifiable->fcm_token, $payload);
        }

        return $payload;
    }

    private function sendPushNotification(string $token, array $payload): void
    {
        $notification = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $payload['author']['name'] . ' sent a message',
                    'body' => $payload['body'] ?: 'New attachment',
                ],
                'data' => [
                    'thread_id' => $payload['thread_id'],
                    'message_id' => $payload['message_id'],
                    'type' => 'chat_message',
                ],
            ],
        ];

        Helpers::pushNotification($notification);
    }
}
