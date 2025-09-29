<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'escrow_id',
        'type',
        'direction',
        'amount',
        'currency',
        'reference',
        'gateway_id',
        'actor_id',
        'notes',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
