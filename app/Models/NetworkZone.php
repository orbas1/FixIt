<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class NetworkZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'type',
        'risk_level',
        'ip_ranges',
        'device_tags',
        'enforced_controls',
        'is_active',
        'description',
    ];

    protected $casts = [
        'ip_ranges' => 'array',
        'device_tags' => 'array',
        'enforced_controls' => 'array',
        'is_active' => 'boolean',
    ];

    public function accessEvents(): HasMany
    {
        return $this->hasMany(ZeroTrustAccessEvent::class);
    }

    public function normalizedIpRanges(): array
    {
        return array_values(array_filter(array_map(static function ($range) {
            return is_string($range) ? trim($range) : null;
        }, Arr::wrap($this->ip_ranges))));
    }

    public function isTrusted(): bool
    {
        return $this->type === 'trusted';
    }
}
