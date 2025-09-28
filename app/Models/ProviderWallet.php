<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderWallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'balance',
    ];

    protected $casts = [
        'provider_id' => 'integer',
        'balance' => 'float',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function transactions(): HasMany
    {
        return $this->hasMany(ProviderTransaction::class, 'provider_wallet_id')->orderBy('created_at', 'desc');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
