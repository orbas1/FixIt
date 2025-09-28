<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Advertisement extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, LogsActivity,HandlesLegacyTranslations;


    public $translatable = [
        'video_link',
    ];

    protected $fillable = [
        'provider_id',
        'images',
        'type',
        'screen',
        'status',
        'start_date',
        'end_date',
        'created_by',
        'zone',
        'banner_type',
        'video_link',
        'price'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at'
    ];

    // protected $with = [
    //     'media',
    //     'services'
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

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'advertisement_services');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function zone_id(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Advertisement')
            ->setDescriptionForEvent(fn(string $eventName) => "Advertisement for {$this->type} has been {$eventName}");
    }
}
