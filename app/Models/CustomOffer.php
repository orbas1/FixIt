<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomOffer extends Model
{
    use HasFactory;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'custom_offers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'is_servicemen_required',
        'required_servicemen',
        'price',
        'category_ids',
        'provider_id',
        'user_id',
        'service_id',
        'status',
        'is_expired',
        'started_at',
        'ended_at',
        'duration',
        'duration_unit',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_servicemen_required' => 'boolean',
        'is_expired' => 'boolean',
        'initial_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'started_at' => 'date',
        'ended_at' => 'date',
        'category_ids' => 'json',
    ];

    // protected $with = [
    //     'user',
    //     'provider',
    //     'service'
    // ];

    /**
     * Get the provider associated with the custom offer.
     */
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get the user associated with the custom offer.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
