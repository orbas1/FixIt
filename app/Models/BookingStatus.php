<?php

namespace App\Models;

use App\Enums\BookingEnum;
use App\Enums\RoleEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatus extends Model
{
    use HasFactory, Sluggable;

    protected $table = 'booking_status';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'sequence',
        'created_by_id',
        'system_reserve',
        'hexa_code',
    ];

    protected $casts = [
        'status' => 'integer',
        'sequence' => 'integer',
        'created_by_id' => 'integer',
    ];

    // protected $append = [
    //     'color_code',
    // ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = auth()->user()->id ?? User::role(RoleEnum::ADMIN)->first()->id;
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function getColorCodeAttribute()
    {
        switch ($this->name) {
            case BookingEnum::PENDING:
                return 'FDB448';
            case BookingEnum::ASSIGNED:
                return 'AD46FF';
            case BookingEnum::ON_THE_WAY:
                return 'FF7456';
            // case BookingEnum::DECLINE:
            //     return 'FF4B4B';
            case BookingEnum::CANCEL:
                return 'FF4B4B';
            case BookingEnum::ON_HOLD:
                return 'FF1D53';
            case BookingEnum::START_AGAIN:
                return 'FDB448';
            case BookingEnum::COMPLETED:
                return '5465FF';
            // case BookingEnum::PENDING_APPROVAL:
            //     return 'FDB448';
            case BookingEnum::ON_GOING:
                return 'FF7456';
            case BookingEnum::ACCEPTED:
                return '48BFFD';
            default:
                return 'FDB448';
        }
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(BookingStatusLog::class, 'booking_status_id');
    }
}
