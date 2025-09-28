<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Testimonial extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The Review that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'rating',
        'description',
        'created_at',
    ];

    // protected $with = [
    //     'media',
    // ];

    protected $casts = [
        'rating' => 'integer',
    ];
}
