<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'cart';

    protected $fillable = [
        'required_servicemen',
        'service_id',
        'service_type',
        'date_time',
        'address_id',
        'custom_message',
        'customer_id'
    ];

    protected $casts = [
        'required_servicemen' => 'integer',
        'service_id' => 'integer',
        'address_id' => 'integer',
        'customer_id' => 'integer',
    ];

    // protected $with = [
    //     'servicemen'
    // ];

    public function servicemen(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cart_servicemen', 'cart_id' , 'serviceman_id');
    }

    public function service() : BelongsTo
    {
      return $this->belongsTo(Service::class, 'service_id');
    }

    public function address() : BelongsTo
    {
      return $this->belongsTo(Address::class,'address_id');
    }

    public function customer() : BelongsTo
    {
      return $this->belongsTo(User::class, 'customer_id');
    }


}
