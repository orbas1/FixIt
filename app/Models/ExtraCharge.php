<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'booking_id',
        'per_service_amount',
        'no_service_done',
        'payment_method',
        'payment_status',
        'total',
        'tax_amount',
        'platform_fees',
        'grand_total',
    ];

    protected $casts = [
        'per_service_amount' => 'float',
        'total' => 'float',
        'booking_id' => 'integer',
        'no_service_done' => 'integer',
        'tax_amount' => 'float',        
        'platform_fees' => 'float',
        'grand_total' => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
