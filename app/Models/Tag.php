<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tag extends Model
{
    use HasFactory, Sluggable, HasTranslations, LogsActivity, HandlesLegacyTranslations;

    public $translatable = [
        'name',
        'description',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'description',
        'created_by_id',
    ];

    protected $casts = [
        'status' => 'integer',
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
                'source' => 'name',
            ],
        ];
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = $this->handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }

    /**
     * @return int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('tag')->id;
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_tags');
    }

    /**
     * @return belongsToMany
     */
    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_tags');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Tag')
            ->setDescriptionForEvent(fn(string $eventName) => "{$this->name} - Tag has been {$eventName}");
    }

}
