<?php

namespace App\Models;

use App\Http\Traits\HandlesLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServiceFAQ extends Model
{
    use HasFactory, HasTranslations, HandlesLegacyTranslations;
    protected $table = 'service_faqs';

    public $translatable = [
        'question',
        'answer',
    ];

    protected $fillable = [
        'answer',
        'question',
        'service_id',
    ];

    protected $casts = [
        'service_id' => 'integer',
    ];

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = $this->handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
