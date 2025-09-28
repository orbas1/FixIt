<?php

namespace App\Models;

use App\Enums\SymbolPositionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Currency extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public $fillable = [
        'code',
        'symbol',
        'symbol_position',
        'no_of_decimal',
        'exchange_rate',
        'system_reserve',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'exchange_rate' => 'float',
        'no_of_decimal' => 'integer',
        'system_reserve' =>  'integer',
        'symbol_position' => SymbolPositionEnum::class,
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_at'
    ];
}
