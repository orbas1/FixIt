<?php

namespace Modules\Coupon\Entities;

use App\Models\Service;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'used',
        'type',
        'amount',
        'is_expired',
        'start_date',
        'end_date',
        'is_first_order',
        'status',
        'is_apply_all',
        'services',
        'exclude_services',
        'min_spend',
        'is_unlimited',
        'usage_per_coupon',
        'usage_per_customer',
    ];

    protected $casts = [
        'min_spend' => 'integer',
        'amount' => 'float',
        'usage_per_customer' => 'integer',
        'is_expired' => 'integer',
        'is_first_order' => 'integer',
        'is_unlimited' => 'integer',
        'status' => 'integer',
        'is_apply_all' => 'integer',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public $with = [
        // 'services',
        // 'exclude_services',
        // 'users:id,name',
        // 'zones:id,name',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = auth()->user()->id;
        });
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'services_coupons');
    }

    public function exclude_services()
    {
        return $this->belongsToMany(Service::class, 'exclude_services_coupons');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_users', 'coupon_id');
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'coupon_zones', 'coupon_id');
    }
}
