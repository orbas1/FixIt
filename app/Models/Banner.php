<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Banner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, HandlesLegacyTranslations, LogsActivity;

    public $translatable = [
        'title',
    ];

    protected $fillable = [
        'title',
        'images',
        'type',
        'related_id',
        'service_id',
        'category_id',
        'provider_id',
        'is_offer',
        'status',
        'created_by'
    ];

    protected $casts = [
        'images' => 'array',
        'status' => 'integer',
        'is_offer' => 'integer',
        'related_id' => 'integer',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    // protected $with = [
    //     'media',
    //     'zones:id,name'
    // ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $user = auth()->user();
            if ($user) {
                $model->created_by = $user->id;
            }
        });
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');

        if (isset($attributes['media']) && is_array($attributes['media'])) {
            $attributes['media'] = array_filter($attributes['media'], function ($media) use ($locale) {
                return isset($media['custom_properties']['language']) && $media['custom_properties']['language'] === $locale;
            });

            $attributes['media'] = array_values($attributes['media']);
        }

        return  $this->handleModelTranslations($this, $attributes, $this->translatable);
    }

    /**
     * @return BelongsTo
     */
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsToMany
     */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'banner_zones');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Banner')
            ->setDescriptionForEvent(fn(string $eventName) => "{$this->title} - Banner has been {$eventName}");
    }
}
