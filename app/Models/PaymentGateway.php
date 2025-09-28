<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'description',
        'serial',
        'mode',
        'configs',
        'status',
    ];

    protected $casts = [
        'configs' => 'json',
        'status' => 'integer',
    ];
}
