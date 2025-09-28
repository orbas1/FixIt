<?php

namespace App\Models;

use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bid extends Model
{
    use HasFactory;

    protected $table = 'bids';

    protected $fillable = [
        'id',
        'service_request_id',
        'provider_id',
        'amount',
        'description',
        'status'
    ];

    // protected $with = [
    //     'provider:id,name,email'
    // ];

    protected $casts = [
        'service_request_id' => 'integer',
        'provider_id' => 'integer',
        'amount' => 'float',
    ];

    /**
     * Get the ride request that owns the bid.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    /**
     * Get the driver that owns the bid.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
