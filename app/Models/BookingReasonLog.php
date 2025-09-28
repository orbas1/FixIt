<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingReasonLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'status_id',
        'reason',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'status_id' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function status()
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }
}
