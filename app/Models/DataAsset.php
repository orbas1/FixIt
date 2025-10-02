<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builders\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataAsset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'data_domain_id',
        'steward_id',
        'key',
        'name',
        'classification',
        'processing_purpose',
        'data_elements',
        'lawful_bases',
        'retention_period_days',
        'requires_dpia',
        'residency_exceptions',
        'monitoring_controls',
        'next_review_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_elements' => 'array',
        'lawful_bases' => 'array',
        'retention_period_days' => 'integer',
        'requires_dpia' => 'boolean',
        'residency_exceptions' => 'array',
        'monitoring_controls' => 'array',
        'next_review_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $asset): void {
            if (empty($asset->ulid)) {
                $asset->ulid = (string) Str::ulid();
            }
        });
    }

    public function scopeDueForReview(Builder $query): Builder
    {
        return $query->whereNotNull('next_review_at')->where('next_review_at', '<=', now());
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(DataDomain::class, 'data_domain_id');
    }

    public function steward(): BelongsTo
    {
        return $this->belongsTo(User::class, 'steward_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function residencyPolicies(): HasMany
    {
        return $this->hasMany(DataResidencyPolicy::class);
    }

    public function dpiaRecords(): HasMany
    {
        return $this->hasMany(DpiaRecord::class);
    }

    public function activePolicies(): BelongsToMany
    {
        return $this->belongsToMany(DataResidencyZone::class, 'data_residency_policies')
            ->withPivot([
                'storage_role',
                'lawful_basis',
                'encryption_profile',
                'data_controller',
                'cross_border_allowed',
                'transfer_safeguards',
                'audit_controls',
            ])
            ->whereNull('data_residency_policies.deleted_at');
    }
}
