<?php

namespace App\Models;

use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Spatie\Translatable\HasTranslations;

class Zone extends Model
{
    use HasFactory, HasSpatial, SoftDeletes, HasTranslations, HandlesLegacyTranslations;

    public $translatable = [
        'name',
    ];

    protected $fillable = [
        'id',
        'name',
        'place_points',
        'locations',
        'status',
        'created_by_id',
        'currency_id',
        'payment_methods'
    ];

    protected $spatialFields = [
        'place_points',
    ];

    protected $casts = [
        'place_points' => Polygon::class,
        'locations' => 'json',
        'status' => 'string',
        'payment_methods' => 'json'
    ];

    public $with = [
        // 'currency',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = auth()?->user()?->id;
        });
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = $this->handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_zones');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
