<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'flaggable_type',
        'flaggable_id',
        'category',
        'severity',
        'action',
        'reasons',
        'snapshot',
        'detected_at',
        'resolved_at',
        'resolved_by_id',
        'resolution_notes',
    ];

    protected $casts = [
        'reasons' => 'array',
        'snapshot' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function flaggable(): MorphTo
    {
        return $this->morphTo();
    }
}
