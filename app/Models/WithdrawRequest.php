<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WithdrawRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'amount',
        'message',
        'status',
        'provider_wallet_id',
        'is_used',
        'payment_type',
        'provider_id',
        'admin_message',
    ];

    protected $casts = [
        'amount' => 'float',
        'message' => 'string',
        'admin_message' => 'string',
        'provider_wallet_id' => 'integer',
        'provider_id' => 'integer',
    ];

    // protected $with = [
    //     'user',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Withdraw Request')
            ->setDescriptionForEvent(fn(string $eventName) => "Withdraw Request from {$this->user?->name} has been {$eventName}");
    }
}
