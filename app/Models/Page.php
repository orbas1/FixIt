<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, Sluggable, HasTranslations, HandlesLegacyTranslations;

    public $translatable = [
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'title',
        'content',
        'slug',
        'image',
        'status',
        'app_type',
        'created_by_id',
        'meta_title',
        'meta_description',
    ];

    // protected $with = [
    //     'media'
    // ];

    protected $casts = [
        'created_by_id' => 'integer',
        'status' => 'integer',
    ];

    protected $hidden = [
        'meta_title',
        'meta_description',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
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
}
