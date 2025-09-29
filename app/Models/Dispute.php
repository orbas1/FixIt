<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Dispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'escrow_id',
        'service_request_id',
        'opened_by_id',
        'against_id',
        'stage',
        'status',
        'reason_code',
        'summary',
        'resolution',
        'deadline_at',
        'closed_at',
        'sla_timer_started_at',
        'metadata',
    ];

    protected $casts = [
        'resolution' => 'array',
        'metadata' => 'array',
        'deadline_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_timer_started_at' => 'datetime',
    ];

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    public function against(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DisputeEvent::class);
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    public function isOverdue(Carbon $now): bool
    {
        return $this->deadline_at !== null && $now->greaterThan($this->deadline_at);
    }
}
