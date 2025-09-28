<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicemanWallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'serviceman_id',
        'balance',
    ];

    protected $casts = [
        'serviceman_id' => 'integer',
        'balance' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(ServicemanTransaction::class, 'serviceman_wallet_id')->orderBy('created_at', 'desc');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }
}
