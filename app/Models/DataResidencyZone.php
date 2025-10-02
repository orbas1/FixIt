<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataResidencyZone extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'code',
        'name',
        'region',
        'country_codes',
        'default_controller',
        'approved_services',
        'risk_rating',
        'is_active',
    ];

    protected $casts = [
        'country_codes' => 'array',
        'approved_services' => 'array',
        'risk_rating' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $zone): void {
            if (empty($zone->ulid)) {
                $zone->ulid = (string) Str::ulid();
            }
        });
    }

    public function policies(): HasMany
    {
        return $this->hasMany(DataResidencyPolicy::class);
    }
}
