<?php

namespace App\Models;

use App\Enums\BidStatusEnum;
use App\Models\Bid;
use App\Models\ModerationFlag;
use App\Services\Geo\H3IndexService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
        'location_coordinates',
        'attachments',
        'latitude',
        'longitude',
        'h3_index'
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
        'location_coordinates' => 'array',
        'attachments' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'h3_index' => 'integer',
        // 'user' => 'json',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (!$model->created_by_id) {
                $model->created_by_id = auth()?->user()?->id;
            }

            $model->synchroniseLocationCoordinates();
        });
    }

    protected function synchroniseLocationCoordinates(): void
    {
        $coordinates = $this->location_coordinates;

        if (is_string($coordinates)) {
            $decoded = json_decode($coordinates, true);
            $coordinates = is_array($decoded) ? $decoded : null;
            $this->location_coordinates = $coordinates;
        }

        if (is_array($coordinates)) {
            if ($this->latitude === null && array_key_exists('lat', $coordinates)) {
                $this->latitude = $coordinates['lat'] !== null ? (float) $coordinates['lat'] : null;
            }

            if ($this->longitude === null && array_key_exists('lng', $coordinates)) {
                $this->longitude = $coordinates['lng'] !== null ? (float) $coordinates['lng'] : null;
            }
        } elseif ($this->latitude !== null && $this->longitude !== null) {
            $this->location_coordinates = [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        if ($this->latitude !== null && $this->longitude !== null) {
            /** @var H3IndexService $h3Service */
            $h3Service = app(H3IndexService::class);
            $this->h3_index = $h3Service->indexFor(
                (float) $this->latitude,
                (float) $this->longitude,
                (int) config('services.h3.feed_resolution', 8)
            );
        } else {
            $this->h3_index = null;
        }
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

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class, 'service_request_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'service_request_id');
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

    public function moderationFlags(): MorphMany
    {
        return $this->morphMany(ModerationFlag::class, 'flaggable');
    }

    public function getAcceptedBid()
    {
        return $this->bids()?->where('status', BidStatusEnum::ACCEPTED)?->first();
    }
}
