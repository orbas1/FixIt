<?php

namespace App\Models;

use App\Http\Traits\HandlesLegacyTranslations;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use HasFactory, HasTranslations, HandlesLegacyTranslations;

    public $translatable = [
        'name',
    ];

    protected $fillable = [
        'id',
        'name',
        'rate',
        'status',
        'zone_id',
        'created_by_id',
    ];

    protected $casts = [
        'rate' => 'integer',
        'status' => 'integer',
        'created_by_id' => 'integer',
        'zone_id' => 'integer',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_at',
        'created_by_id'
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

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
