<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class SystemLang extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'flag',
        'locale',
        'app_locale',
        'is_rtl',
        'system_reserve',
        'status',
    ];

    protected $appends = [
        'flag_path'
    ];

    protected $casts = [
        'status' => 'integer',
        'is_rtl' => 'integer',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_at'
    ];

    public static function boot()
    {
        parent::boot();
        static::created(function ($language) {
            self::createLangFolder($language);
        });

        static::deleting(function ($language) {
            self::deleteLangFolder($language);
        });

        static::saving(function ($language) {
            if (Helpers::isDefaultLang($language?->id)) {
                Session::put('dir', $language?->is_rtl ? 'rtl' : 'ltr');
            }
        });
    }

    public function getFlagPathAttribute()
    {
        return  'admin/images/flags'.'/'.$this->attributes['flag'];
    }

    public function getFlagAttribute($value)
    {
        return isset($value) ? asset('admin/images/flags').'/'.$value : null;
    }

    public function setValuesAttribute($value)
    {
        $this->attributes['flag'] = $value;
    }

    public static function createLangFolder($language)
    {
        $langDir = resource_path().'/lang/';
        $enDir = $langDir.(app()?->getLocale());
        $currentLang = $langDir.$language->locale;
        if (! File::exists($currentLang)) {
            File::makeDirectory($currentLang);
            File::copyDirectory($enDir, $currentLang);
        }
    }

    public static function deleteLangFolder($language)
    {
        if(config('app.demo')) {
            $folderURL = resource_path().'/lang/'.$language->locale;
            if (File::exists($folderURL)) {
                File::deleteDirectory($folderURL);
            }
        }
    }
}
