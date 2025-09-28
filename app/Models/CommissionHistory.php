<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommissionHistory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_commission',
        'provider_commission',
        'booking_id',
        'provider_id',
        'category_id',
    ];

    // protected $with = [
    //     'booking',
    //     'provider',
    //     'serviceman_commissions.serviceman:id,name'
    // ];

    protected $casts = [
        'admin_commission' => 'float',
        'provider_commission' => 'float',
        'booking_id' => 'integer',
        'provider_id' => 'integer',
        'category_id' => 'integer',
        'status' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function provider()
    {
        return $this->hasOne(User::class, 'id', 'provider_id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function serviceman_commissions(): HasMany
    {
        return $this->hasMany(ServicemanCommissions::class, 'commission_history_id');
    }

    public function calculateServicemanCommissions(): float
    {
        return $this->serviceman_commissions()->sum('commission');
    }

    public function getProviderNetCommissionAttribute(): float
    {
        $servicemanTotal = $this->serviceman_commissions()->sum('commission');
        return round($this->provider_commission - $servicemanTotal, 2);
    }
}
