<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DpiaRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'data_asset_id',
        'status',
        'risk_level',
        'risk_score',
        'assessment_summary',
        'mitigation_actions',
        'residual_risks',
        'submitted_at',
        'approved_at',
        'reviewed_by',
        'next_review_at',
    ];

    protected $casts = [
        'risk_score' => 'integer',
        'mitigation_actions' => 'array',
        'residual_risks' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $record): void {
            if (empty($record->ulid)) {
                $record->ulid = (string) Str::ulid();
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(DataAsset::class, 'data_asset_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(DpiaFinding::class);
    }
}
