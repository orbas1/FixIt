<?php

namespace App\Models;

use App\Enums\EscrowStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Escrow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_request_id',
        'consumer_id',
        'provider_id',
        'status',
        'amount',
        'amount_released',
        'amount_refunded',
        'currency',
        'hold_reference',
        'metadata',
        'funded_at',
        'released_at',
        'refunded_at',
        'cancelled_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'amount_released' => 'float',
        'amount_refunded' => 'float',
        'metadata' => 'array',
        'funded_at' => 'datetime',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = ['available_amount'];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EscrowTransaction::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function getAvailableAmountAttribute(): float
    {
        return round(max(0.0, ($this->amount ?? 0.0) - ($this->amount_released ?? 0.0) - ($this->amount_refunded ?? 0.0)), 2);
    }

    public function markExpiredIfNecessary(Carbon $now): void
    {
        if ($this->status === EscrowStatusEnum::FUNDED && $this->expires_at && $now->greaterThan($this->expires_at)) {
            $this->status = EscrowStatusEnum::REQUIRES_ACTION;
        }
    }
}
