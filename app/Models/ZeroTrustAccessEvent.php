<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZeroTrustAccessEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trusted_device_id',
        'network_zone_id',
        'request_ip',
        'user_agent',
        'decision',
        'risk_score',
        'signals',
        'policy_version',
        'mfa_verified_at',
        'enforced_controls',
    ];

    protected $casts = [
        'signals' => 'array',
        'mfa_verified_at' => 'datetime',
        'enforced_controls' => 'array',
        'risk_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(TrustedDevice::class, 'trusted_device_id');
    }

    public function networkZone(): BelongsTo
    {
        return $this->belongsTo(NetworkZone::class);
    }
}
