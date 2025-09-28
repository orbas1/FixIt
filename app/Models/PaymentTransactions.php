<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PaymentTransactions extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'payment_gateways_transactions';

    protected $fillable = [
        'item_id',
        'amount',
        'transaction_id',
        'payment_method',
        'payment_status',
        'type',
        'request_type',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'amount' => 'float',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Payment Transaction')
            ->setDescriptionForEvent(fn(string $eventName) => "Payment Transaction for {$this->transaction_id} -  has been {$eventName}");
    }
}
