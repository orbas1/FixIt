<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'code',
        'description',
    ];

    // protected $with = [
    //     'media',
    // ];

    protected $casts = [
        'phone' => 'integer',
        'code' => 'string',
    ];

    // protected $appends = [
    //     'primary_address',
    // ];

    public function addresses()
    {
        return $this->hasMany(Address::class, 'company_id', 'id');
    }

    public function getPrimaryAddressAttribute()
    {
        return $this->addresses()->where('is_primary', true)->first();
    }

    public function serviceAvailabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
