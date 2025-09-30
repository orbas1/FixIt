<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MfaChallenge extends Model
{
    protected $table = 'mfa_challenges';

    protected $fillable = [
        'id',
        'user_id',
        'status',
        'attempts',
        'expires_at',
        'resolved_at',
        'resolved_via',
        'metadata',
        'request_ip',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (self $challenge): void {
            if (!$challenge->getKey()) {
                $challenge->setAttribute($challenge->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasExceededAttempts(int $limit): bool
    {
        return $this->attempts >= $limit;
    }

    public function recordAttempt(bool $successful, ?string $resolvedVia = null): void
    {
        if ($successful) {
            $this->forceFill([
                'status' => 'resolved',
                'resolved_at' => Carbon::now(),
                'resolved_via' => $resolvedVia,
            ])->save();

            return;
        }

        $this->forceFill([
            'attempts' => $this->attempts + 1,
            'status' => $this->attempts + 1 >= config('security.mfa.max_attempts') ? 'locked' : $this->status,
        ])->save();
    }

    public function expire(): void
    {
        if ($this->status !== 'resolved') {
            $this->forceFill(['status' => 'expired'])->save();
        }
    }
}
