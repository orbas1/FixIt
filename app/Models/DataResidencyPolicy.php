<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataResidencyPolicy extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'data_asset_id',
        'data_residency_zone_id',
        'storage_role',
        'lawful_basis',
        'encryption_profile',
        'data_controller',
        'cross_border_allowed',
        'transfer_safeguards',
        'audit_controls',
    ];

    protected $casts = [
        'cross_border_allowed' => 'boolean',
        'transfer_safeguards' => 'array',
        'audit_controls' => 'array',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(DataAsset::class, 'data_asset_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DataResidencyZone::class, 'data_residency_zone_id');
    }
}
