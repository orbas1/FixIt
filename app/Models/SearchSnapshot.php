<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'document',
    ];

    protected $casts = [
        'document' => AsArrayObject::class,
    ];
}
