<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicemanTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'from',
        'amount',
        'detail',
        'serviceman_id',
        'serviceman_wallet_id',
    ];

    protected $casts = [
        'serviceman_wallet_id' => 'integer',
        'serviceman_id' => 'integer',
        'amount' => 'float',
        'from' => 'integer',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function servicemanWallet(): HasMany
    {
        return $this->hasMany(ServicemanWallet::class, 'serviceman_wallet_id');
    }
}
