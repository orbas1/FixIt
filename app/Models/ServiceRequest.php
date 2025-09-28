<?php

namespace App\Models;

use App\Models\Bid;
use App\Enums\BidStatusEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceRequest extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'service_requests';

    protected $fillable = [
        'title',
        'description',
        'duration',
        'duration_unit',
        'required_servicemen',
        'initial_price',
        'final_price',
        'status',
        'service_id',
        'user_id',
        'provider_id',
        'created_by_id',
        'booking_date',
        'category_ids',
        'locations',
        'location_coordinates'
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'duration' => 'string',
        'duration_unit' => 'string',
        'required_servicemen' => 'integer',
        'initial_price' => 'float',
        'final_price' => 'float',
        'status' => 'string',
        'service_id' => 'integer',
        'user_id' => 'integer',
        'provider_id' => 'integer',
        'created_by_id' => 'integer',
        'booking_date' => 'datetime',
        'category_ids' => 'json',
        'locations' => 'json',
        'location_coordinates' => 'json',
        // 'user' => 'json',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = auth()?->user()?->id;
        });
    }

    public function getCategoriesDataAttribute()
    {
        if (is_array($this->category_ids) && count($this->category_ids)) {
            return Category::whereIn('id', $this->category_ids)
                ->get(['id', 'title']);
        }

        return collect([]);
    }

    /**
     * @return HasMany
     */
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'service_request_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'service_request_zones');
    }

    public function getAcceptedBid()
    {
        return $this->bids()?->where('status', BidStatusEnum::ACCEPTED)?->first();
    }
}
