<?php

namespace Modules\Subscription\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'user_subscriptions';

    protected $fillable = [
        'user_id',
        'user_plan_id',
        'start_date',
        'end_date',
        'total',      
        'allowed_max_services',
        'allowed_max_addresses',
        'allowed_max_servicemen',
        'allowed_max_service_packages',
        'is_included_free_trial',
        'is_active',
        'payment_method',
        'payment_status',
        'product_id',
        'in_app_status',
        'in_app_price',
        'source'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function hasActiveSubscription($userId)
    {
        return self::where('user_id', $userId)->active()->exists();
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'user_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function calculateEndDate($duration, $addDays = null)
    {
        $duration = $duration === 'monthly' ? 'month' : 'year';
        $date = Carbon::now()->add(1, $duration);
        if ($addDays) {
            $date->addDays($addDays);
        }

        return $date;
    }
}
