<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicemanWithdrawRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'amount',
        'message',
        'status',
        'serviceman_wallet_id',
        'is_used_by_admin',
        'is_used',
        'payment_type',
        'serviceman_id',
        'admin_message',
    ];

    protected $casts = [
        'amount' => 'float',
        'message' => 'string',
        'admin_message' => 'string',
        'is_used' => 'integer',
        'is_used_by_admin' => 'integer',
        'serviceman_wallet_id' => 'integer',
        'serviceman_id' => 'integer',
    ];

    // protected $with = [
    //     'user',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }
}
