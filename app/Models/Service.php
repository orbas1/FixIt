<?php

namespace App\Models;

use App\Enums\EarthRadius;
use App\Enums\FrontEnum;
use App\Helpers\Helpers;
use App\Http\Traits\HandlesLegacyTranslations;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Service extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, LogsActivity, SoftDeletes, HandlesLegacyTranslations, HasTranslations;

    protected $primaryKey = 'id';

    public $translatable = [
        'title',
        'description',
        'content',
        'speciality_description',
        'meta_title',
        'meta_description',
        'video'
    ];

    protected $fillable = [
        'title',
        'video',
        'price',
        'status',
        'duration',
        'duration_unit',
        'discount',
        'per_serviceman_commission',
        'description',
        'content',
        'speciality_description',
        'address_id',
        'user_id',
        'parent_id',
        'type',
        'is_featured',
        'created_by_id',
        'is_random_related_services',
        'meta_title',
        'meta_description',
        'service_rate',
        'slug',
        'required_servicemen',
        'service_type',
        'is_advertised',
        'is_favourite',
        'is_custom_offer',
        'destination_location'
    ];

    protected $withCount = [
        'bookings',
        'reviews',
    ];

    // protected $appends = [
    //     'per_serviceman_charge',
    //     'review_ratings',
    //     'rating_count',
    //     'web_img_thumb_url',
    //     'web_img_galleries_url',
    //     'highest_commission',
    //     'is_favourite',
    // ];

    protected $casts = [
        'price' => 'float',
        'status' => 'integer',
        'discount' => 'float',
        'per_serviceman_commission' => 'float',
        'address_id' => 'integer',
        'user_id' => 'integer',
        'is_featured' => 'integer',
        'created_by_id' => 'integer',
        'service_rate' => 'float',
        'discount_amount' => 'float',
        'total_tax_amount' => 'float',
        'required_servicemen' => 'integer',
        'destination_location' => 'json'
    ];

    // public $with = [
    //     'categories',
    //     'media',
    // ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Service')
            ->setDescriptionForEvent(fn(string $eventName) => "{$this->title} - Service has been {$eventName}");
    }

    public function scopeWithoutCustomOffers($query)
    {
        return $query->where('is_custom_offer', false);
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');

        // Filter media based on the language in custom_properties
        if (isset($attributes['media']) && is_array($attributes['media'])) {
            $attributes['media'] = array_filter($attributes['media'], function ($media) use ($locale) {
                return isset($media['custom_properties']['language']) && $media['custom_properties']['language'] === $locale ?? app()->getLocale();
            });

            // Re-index the array to avoid gaps in indices after filtering
            $attributes['media'] = array_values($attributes['media']);
        }

        return  $this->handleModelTranslations($this, $attributes, $this->translatable);
    }

    public function getIsFavouriteAttribute()
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return 0;
        }

        $isFavourite =  FavouriteList::where('consumer_id', $user->id)
            ->where('service_id', $this->id)
            ->exists();

            return (int) $isFavourite;
    }

    public function getIsFavouriteIdAttribute()
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return null;
        }

        return FavouriteList::where('consumer_id', $user->id)
        ->where('service_id', $this->id)
        ->value('id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'service_id', 'id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
                $model->created_by_id = auth()->user()?->id;
        });

        static::deleting(function ($service) {
            $service->media->each(function ($media) {
                $media->delete();
            });
            if (method_exists($service, 'servicePackages')) {
                $service->servicePackages()->detach();
            }
            // $service->servicePackages()->detach();
        });

        static::addGlobalScope('exclude_custom_offers', function ($builder) {
            $builder->where('is_custom_offer', false);
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ],
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_id');
    }

    public function additionalServices()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function parentService()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function validateAdditionalServices($additionalServices)
    {
        return $this->additionalServices()
                ->whereIn('id', $additionalServices)
                ->exists();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'service_categories');
    }

    public function getHighestCommissionAttribute()
    {
        return $this->categories->max('commission');
    }

    public function getPerServicemanChargeAttribute()
    {
        if ($this->required_servicemen > 0) {
            return round($this->service_rate / $this->required_servicemen, 2);
        }
        return 0;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'service_id');
    }

    public function related_services()
    {
        return $this->belongsToMany(Service::class, 'related_services', 'related_service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class,'service_taxes');
    }

    public function getRatingCountAttribute()
    {
        return $this->reviews->avg('rating');
    }

    public function getReviewRatingsAttribute()
    {
        return Helpers::getReviewRatings($this->id);
    }

    public function getWebImgThumbUrlAttribute()
    {
        $locale = app()->getLocale();

        $thumbnail = $this->getMedia('web_thumbnail')->filter(function ($media) use ($locale) {
            return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;
        })->first();
        return $thumbnail ? Helpers::isFileExistsFromURL($thumbnail?->getUrl(), true) : FrontEnum::getPlaceholderImageUrl();
    }

    public function getWebImgGalleriesUrlAttribute()
    {
        $locale = request()->header('Accept-Lang') ?: app()->getLocale();

        $galleryImages = $this->getMedia('web_images')->filter(function ($media) use ($locale) {
            return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;
        });

        return $galleryImages->isNotEmpty() ? $galleryImages->pluck('original_url')->toArray() : [FrontEnum::getPlaceholderImageUrl()];
    }
    public static function calculateDistance($serviceLat, $serviceLong, $userLat, $userLong)
    {
        $earthRadius = EarthRadius::EARTHRADIUS;
        $serviceLat = deg2rad($serviceLat);
        $serviceLon = deg2rad($serviceLong);
        $userLat = deg2rad($userLat);
        $userLon = deg2rad($userLong);

        $distanceLat = $userLat - $serviceLat;
        $distanceLon = $userLon - $serviceLon;

        $angularDistanceSquared = sin($distanceLat / 2) ** 2 + cos($serviceLat) * cos($userLat) * sin($distanceLon / 2) ** 2;
        $centralAngle = 2 * asin(sqrt($angularDistanceSquared));

        $distance = $earthRadius * $centralAngle;

        return $distance;
    }

    public function serviceAvailabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    public function faqs()
    {
        return $this->hasMany(ServiceFAQ::class, 'service_id', 'id');
    }

    public function getDiscountAmountAttribute(): string
    {
        return number_format($this->price - $this->service_rate, 2, '.', '');
    }

    public function getTotalTaxAmountAttribute(): string
    {
        $taxTotal = $this->taxes->sum('rate');
        return number_format($taxTotal, 2, '.', '');
    }
}
