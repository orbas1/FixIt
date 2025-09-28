<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id',
        'booking_id',
        'detail',
        'amount',
        'type',
        'from',
    ];

    protected $casts = [
        'wallet_id' => 'integer',
        'booking_id' => 'integer',
        'amount' => 'float',
        'from' => 'integer',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * @return int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('wallet')->id;
    }

    public function wallet(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'id', 'booking_id')->select('id', 'bookin_number');
    }

    public function from(): HasOne
    {
        return $this->hasOne(User::class, 'from');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Transaction')
            ->setDescriptionForEvent(fn(string $eventName) => "Transaction for {$this->booking_id} has been {$eventName}");
    }

}
