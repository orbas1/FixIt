<?php

namespace App\Models;

use App\Enums\SecurityIncidentSeverity;
use App\Enums\SecurityIncidentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SecurityIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'title',
        'severity',
        'status',
        'detection_source',
        'impacted_assets',
        'timeline',
        'impact_summary',
        'mitigation_steps',
        'root_cause',
        'follow_up_actions',
        'runbook_updates',
        'detected_at',
        'acknowledged_at',
        'resolved_at',
        'created_by',
        'acknowledged_by',
        'resolved_by',
    ];

    protected $casts = [
        'impacted_assets' => 'array',
        'timeline' => 'array',
        'follow_up_actions' => 'array',
        'runbook_updates' => 'array',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $incident) {
            if (blank($incident->public_id)) {
                $incident->public_id = (string) Str::uuid();
            }

            if (! in_array($incident->severity, SecurityIncidentSeverity::values(), true)) {
                $incident->severity = SecurityIncidentSeverity::MEDIUM->value;
            }

            if (! in_array($incident->status, SecurityIncidentStatus::values(), true)) {
                $incident->status = SecurityIncidentStatus::OPEN->value;
            }
        });
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function appendTimeline(string $event, array $context = []): void
    {
        $timeline = Arr::wrap($this->timeline);

        $timeline[] = [
            'event' => $event,
            'context' => $context,
            'occurred_at' => now()->toIso8601String(),
        ];

        $this->timeline = $timeline;
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function severity(): SecurityIncidentSeverity
    {
        return SecurityIncidentSeverity::from($this->severity);
    }

    public function status(): SecurityIncidentStatus
    {
        return SecurityIncidentStatus::from($this->status);
    }
}
