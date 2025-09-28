<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class HomePage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations;

    protected $casts = [
        'content' => 'json',
    ];

    /**
     * The Options that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'slug',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function toArray($locale = null)
    {
        $attributes = parent::toArray();
        $locale = $locale ?? app()->getLocale();
        foreach ($this->getTranslatableAttributes() as $name) {
            $translation = $this->getTranslation($name, $locale);
            $attributes[$name] = $translation ?? $attributes[$name];
        }

        return $attributes;
    }
}
