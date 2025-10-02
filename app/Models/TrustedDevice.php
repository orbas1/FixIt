<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TrustedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_identifier',
        'display_name',
        'platform',
        'trust_level',
        'approved_at',
        'revoked_at',
        'last_seen_at',
        'signals',
        'enrolled_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'signals' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null && $this->revoked_at === null;
    }

    public function approve(?string $enrolledBy = null): void
    {
        $this->forceFill([
            'trust_level' => 'trusted',
            'approved_at' => Carbon::now(),
            'enrolled_by' => $enrolledBy,
        ])->save();
    }

    public function revoke(): void
    {
        $this->forceFill([
            'trust_level' => 'revoked',
            'revoked_at' => Carbon::now(),
        ])->save();
    }
}
