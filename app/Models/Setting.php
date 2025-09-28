<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Arr;

class Setting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'values',
    ];

    protected $casts = [
        'values' => 'json',
    ];

    /**
     * The values that are mass assignable.
     *
     * @var array
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('settings');
    }

    public function getValuesAttribute($value)
    {
        $values = json_decode($value, true);
        $defaultCurrency = Currency::find($values['general']['default_currency_id']);
        $defaultLang = SystemLang::find($values['general']['default_language_id']);
        $values['general']['default_currency'] = $defaultCurrency;
        $values['general']['default_language'] = $defaultLang;
        return $values;
    }

    public function setValuesAttribute($value)
    {
        $this->attributes['values'] = json_encode($value);
    }
}
