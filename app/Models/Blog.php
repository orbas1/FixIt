<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Enums\FrontEnum;
use App\Http\Traits\HandlesLegacyTranslations;
use Spatie\Translatable\HasTranslations;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Blog extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, SoftDeletes, HasTranslations, HandlesLegacyTranslations, LogsActivity;

    public $translatable = [
        'title',
        'description',
        'content',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'meta_title',
        'meta_description',
        'is_featured',
        'status',
        'created_by_id',
    ];

    // public $with = [
    //     'media',
    //     'categories',
    //     'created_by',
    //     'tags',
    // ];

    protected $casts = [
        'status' => 'integer',
        'is_featured' => 'integer',
        'created_by_id' => 'integer',
    ];

    public $withCount = [
        'comments',
    ];

    // protected $appends = [
    //     'web_img_thumb_url',
    // ];

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
            ],
        ];
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale') ?? app()->getLocale();

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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_categories', 'blog_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_tags', 'blog_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getWebImgThumbUrlAttribute()
    {
        $locale = app()->getLocale();
        $thumbnail = $this->getMedia('web_image')->filter(function ($media) use ($locale) {
            return isset($media->custom_properties['language']) && $media->custom_properties['language'] === $locale;
        })->first();

       return $thumbnail ? Helpers::isFileExistsFromURL($thumbnail?->getUrl(), true) : FrontEnum::getPlaceholderImageUrl();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Blog')
            ->setDescriptionForEvent(fn(string $eventName) => "{$this->title} - Blog has been {$eventName}");
    }
}
