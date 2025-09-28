<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ServiceProof extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'booking_id',
    ];

    protected $casts = [
        'booking_id' => 'integer',
    ];

    // public $with = [
    //     'media',
    // ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
