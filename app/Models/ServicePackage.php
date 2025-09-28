<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use App\Http\Traits\HandlesLegacyTranslations;

class ServicePackage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable,SoftDeletes, HandlesLegacyTranslations, HasTranslations;

    public $translatable = [
        'title',
        'description',
        'meta_title',
        'meta_description',
        'disclaimer',
    ];

    protected $fillable = [
        'title',
        'price',
        'status',
        'is_featured',
        'discount',
        'slug',
        'description',
        'disclaimer',
        'hexa_code',
        'bg_color',
        'meta_title',
        'meta_description',
        'created_by_id',
        'provider_id',
        'started_at',
        'ended_at',
    ];

    // public $with = [
    //     'services',
    //     'media',
    //     'user',
    // ];

    protected $casts = [
        'price' => 'float',
        'status' => 'integer',
        'discount' => 'float',
        'provider_id' => 'integer',
        'is_featured' => 'integer',
        'created_by_id' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = auth()->user()->id;
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

    public function toArray()
    {
        $attributes = parent::toArray();
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');
        // Filter media based on the language in custom_properties
        if (isset($attributes['media']) && is_array($attributes['media'])) {
            $attributes['media'] = array_filter($attributes['media'], function ($media) use ($locale) {
                return isset($media['custom_properties']['language'])
                    && $media['custom_properties']['language'] === $locale;
            });

            // Re-index the array to avoid gaps in indices after filtering
            $attributes['media'] = array_values($attributes['media']);
        }

        return  $this->handleModelTranslations($this, $attributes, $this->translatable);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_package_services');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
}
