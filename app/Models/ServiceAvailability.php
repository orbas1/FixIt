<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAvailability extends Model
{
    use HasFactory;

    protected $table = 'service_availabilities';

    protected $fillable = [
        'company_id',
        'address_id',
        'user_id',
        'service_id',
    ];

    // public $with = [
    //     'company:company_id,name,email',
    //     'address',
    // ];

    protected $casts = [
        'company_id' => 'integer',
        'address_id' => 'integer',
        'user_id' => 'integer',
        'service_id' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
