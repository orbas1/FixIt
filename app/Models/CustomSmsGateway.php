<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomSmsGateway extends Model
{
    use HasFactory;

    /**
     * The values that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'base_url',
        'method',
        'is_config',
        'sid',
        'auth_token',
        'from',
        'configs',
        'params',
        'headers',
        'body',
        'custom_keys'
    ];

    protected $casts = [
        'is_config' => 'json',
        'configs' => 'json',
        'params' => 'json',
        'headers' => 'json',
        'body' => 'json',
        'custom_keys' => 'json',
    ];
}
