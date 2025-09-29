<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'user_id',
        'role',
        'is_active',
        'last_read_at',
        'muted_until',
        'notification_preferences',
    ];

    protected $casts = [
        'thread_id' => 'integer',
        'user_id' => 'integer',
        'is_active' => 'boolean',
        'last_read_at' => 'datetime',
        'muted_until' => 'datetime',
        'notification_preferences' => 'array',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markRead(): void
    {
        $this->forceFill(['last_read_at' => now()])->save();
    }

    public function isMuted(): bool
    {
        return $this->muted_until !== null && $this->muted_until->isFuture();
    }
}
