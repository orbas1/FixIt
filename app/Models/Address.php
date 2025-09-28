<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'latitude',
        'longitude',
        'area',
        'postal_code',
        'city',
        'state_id',
        'status',
        'country_id',
        'address',
        'street_address',
        'is_primary',
        'type',
        'alternative_name',
        'alternative_phone',
        'code',
        'availability_radius',
        'company_id',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_primary' => 'integer',
        'country_id' => 'integer',
        'state_id' => 'integer',
        'alternative_phone' => 'integer',
        'company_id' => 'integer',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    // protected $with = [
    //     'country:id,name',
    //     'state:id,name',
    // ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function booking()
    {
        return $this->hasOne(Booking::class, 'address_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function serviceAvailabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }
}
