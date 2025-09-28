<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'from',
        'amount',
        'detail',
        'provider_id',
        'provider_wallet_id',
    ];

    protected $casts = [
        'provider_wallet_id' => 'integer',
        'provider_id' => 'integer',
        'amount' => 'float',
        'from' => 'integer',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function providerWallet(): HasMany
    {
        return $this->hasMany(providerWallet::class, 'provider_wallet_id');
    }
}
