<?php

namespace Modules\Subscription\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by = auth()->user()->id;
        });
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'max_services',
        'max_addresses',
        'max_servicemen',
        'max_service_packages',
        'price',
        'status',
        'created_by',
        'duration',
        'description',
        'product_id'
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'user_plan_id');
    }
}
