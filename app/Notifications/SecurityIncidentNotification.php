<?php

namespace App\Notifications;

use App\Models\SecurityIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityIncidentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly SecurityIncident $incident, private readonly string $event)
    {
        $this->queue = 'security';
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->event) {
            'resolved' => "Security incident resolved: {$this->incident->title}",
            'acknowledged' => "Security incident acknowledged: {$this->incident->title}",
            default => "New security incident reported: {$this->incident->title}",
        };

        $message = (new MailMessage())
            ->subject($subject)
            ->greeting('Security Operations Update')
            ->line('Severity: ' . ucfirst($this->incident->severity))
            ->line('Status: ' . ucfirst($this->incident->status));

        if (! empty($this->incident->impact_summary)) {
            $message->line('Impact: ' . $this->incident->impact_summary);
        }

        if (! empty($this->incident->mitigation_steps)) {
            $message->line('Mitigation: ' . $this->incident->mitigation_steps);
        }

        return $message->action('Review incident runbook', url('/admin/security/incidents/' . $this->incident->public_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'public_id' => $this->incident->public_id,
            'title' => $this->incident->title,
            'severity' => $this->incident->severity,
            'status' => $this->incident->status,
            'event' => $this->event,
        ];
    }
}
