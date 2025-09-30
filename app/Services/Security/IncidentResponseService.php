<?php

namespace App\Services\Security;

use App\Enums\SecurityIncidentSeverity;
use App\Enums\SecurityIncidentStatus;
use App\Models\SecurityIncident;
use App\Models\User;
use App\Notifications\SecurityIncidentNotification;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class IncidentResponseService
{
    public function __construct(private readonly DatabaseManager $database)
    {
    }

    public function report(array $payload, ?User $actor = null): SecurityIncident
    {
        $attributes = $this->sanitizePayload($payload);
        $incident = new SecurityIncident($attributes);
        $incident->created_by = $actor?->id;
        $incident->detected_at = $attributes['detected_at'] ?? now();
        $incident->status = SecurityIncidentStatus::OPEN->value;

        $this->database->transaction(function () use ($incident, $actor, $payload) {
            $incident->appendTimeline('reported', [
                'actor_id' => $actor?->id,
                'actor_email' => $actor?->email,
                'payload' => Arr::except($payload, ['impact_summary', 'mitigation_steps']),
            ]);
            $incident->save();
        });

        $incident->refresh();

        $this->notifyWatchers($incident, 'reported');

        Log::info('Security incident reported', [
            'incident_id' => $incident->id,
            'public_id' => $incident->public_id,
            'severity' => $incident->severity,
        ]);

        return $incident;
    }

    public function acknowledge(SecurityIncident $incident, ?User $actor = null, ?string $note = null): SecurityIncident
    {
        if ($incident->status() === SecurityIncidentStatus::CLOSED) {
            throw new \DomainException('Closed incidents cannot be acknowledged.');
        }

        $this->database->transaction(function () use ($incident, $actor, $note) {
            $incident->status = SecurityIncidentStatus::ACKNOWLEDGED->value;
            $incident->acknowledged_at = now();
            $incident->acknowledged_by = $actor?->id;
            $incident->appendTimeline('acknowledged', [
                'actor_id' => $actor?->id,
                'note' => $note,
            ]);
            $incident->save();
        });

        $incident->refresh();

        $this->notifyWatchers($incident, 'acknowledged');

        return $incident;
    }

    public function resolve(SecurityIncident $incident, array $payload, ?User $actor = null): SecurityIncident
    {
        if ($incident->status() === SecurityIncidentStatus::CLOSED) {
            throw new \DomainException('Closed incidents cannot be resolved again.');
        }

        $resolution = [
            'root_cause' => Arr::get($payload, 'root_cause', $incident->root_cause),
            'impact_summary' => Arr::get($payload, 'impact_summary', $incident->impact_summary),
            'mitigation_steps' => Arr::get($payload, 'mitigation_steps', $incident->mitigation_steps),
            'follow_up_actions' => Arr::get($payload, 'follow_up_actions', $incident->follow_up_actions),
            'runbook_updates' => Arr::get($payload, 'runbook_updates', $incident->runbook_updates),
        ];

        $this->database->transaction(function () use ($incident, $actor, $resolution) {
            $incident->fill($resolution);
            $incident->status = SecurityIncidentStatus::RESOLVED->value;
            $incident->resolved_at = now();
            $incident->resolved_by = $actor?->id;
            $incident->appendTimeline('resolved', [
                'actor_id' => $actor?->id,
                'resolution' => Arr::except($resolution, ['runbook_updates']),
            ]);
            $incident->save();
        });

        $incident->refresh();

        $this->notifyWatchers($incident, 'resolved');

        return $incident;
    }

    public function close(SecurityIncident $incident, ?User $actor = null, ?string $note = null): SecurityIncident
    {
        $this->database->transaction(function () use ($incident, $actor, $note) {
            $incident->status = SecurityIncidentStatus::CLOSED->value;
            $incident->appendTimeline('closed', [
                'actor_id' => $actor?->id,
                'note' => $note,
            ]);
            $incident->save();
        });

        $incident->refresh();
        $this->notifyWatchers($incident, 'closed');

        return $incident;
    }

    protected function sanitizePayload(array $payload): array
    {
        $severity = Arr::get($payload, 'severity', SecurityIncidentSeverity::MEDIUM->value);
        if (! in_array($severity, SecurityIncidentSeverity::values(), true)) {
            $severity = SecurityIncidentSeverity::MEDIUM->value;
        }

        return [
            'title' => Arr::get($payload, 'title'),
            'severity' => $severity,
            'detection_source' => Arr::get($payload, 'detection_source'),
            'impact_summary' => Arr::get($payload, 'impact_summary'),
            'mitigation_steps' => Arr::get($payload, 'mitigation_steps'),
            'follow_up_actions' => Arr::get($payload, 'follow_up_actions'),
            'runbook_updates' => Arr::get($payload, 'runbook_updates'),
            'impacted_assets' => Arr::get($payload, 'impacted_assets'),
            'detected_at' => $this->parseDetectedAt($payload),
        ];
    }

    protected function parseDetectedAt(array $payload): Carbon
    {
        $detectedAt = Arr::get($payload, 'detected_at');
        if ($detectedAt instanceof Carbon) {
            return $detectedAt;
        }

        return $detectedAt ? Carbon::parse($detectedAt) : now();
    }

    protected function notifyWatchers(SecurityIncident $incident, string $event): void
    {
        $watchers = $this->securityWatchers();
        if ($watchers->isEmpty()) {
            return;
        }

        Notification::send($watchers, new SecurityIncidentNotification($incident, $event));
    }

    protected function securityWatchers(): Collection
    {
        $role = Role::query()
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            return collect();
        }

        return $role->users;
    }
}
