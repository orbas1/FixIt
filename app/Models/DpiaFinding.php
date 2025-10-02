<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpiaFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'dpia_record_id',
        'category',
        'severity',
        'finding',
        'recommendation',
        'status',
        'due_at',
        'mitigated_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'mitigated_at' => 'datetime',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(DpiaRecord::class, 'dpia_record_id');
    }
}
