<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Coupon\Entities\Coupon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Booking extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'id',
        'booking_number',
        'consumer_id',
        'coupon_id',
        'wallet_balance',
        'convert_wallet_balance',
        'provider_id',
        'service_id',
        'service_package_id',
        'service_price',
        'type',
        'tax',
        'per_serviceman_charge',
        'required_servicemen',
        'total_extra_servicemen',
        'total_servicemen',
        'coupon_total_discount',
        'platform_fees',
        'address_id',
        'total_extra_servicemen_charge',
        'subtotal',
        'total',
        'date_time',
        'parent_id',
        'booking_status_id',
        'payment_method',
        'payment_status',
        'description',
        'invoice_url',
        'created_by_id',
        'platform_fees_type',
        'video_consultation_id'
    ];

    // protected $with = [
    //     'provider:id,name,experience_interval,experience_duration,email,phone,fcm_token,code',
    //     'service.addresses',
    //     'servicemen:id,name,experience_interval,experience_duration,email,phone,fcm_token',
    //     'coupon:code,amount,type',
    //     'booking_status:id,name,slug,hexa_code',
    //     'booking_status_logs',
    //     'consumer',
    //     'address',
    //     'bookingReasons',
    //     'serviceProofs',
    //     'extra_charges',
    //     'additional_services',
    // ];

    protected $casts = [
        'amount' => 'float',
        'tax_total' => 'float',
        'total' => 'float',
        'consumer_id' => 'integer',
        'booking_id' => 'integer',
        'coupon_id' => 'integer',
        'booking_status_id' => 'integer',
        'wallet_balance' => 'float',
        'coupon_total_discount' => 'float',
        'status' => 'integer',
        'created_by_id' => 'integer',
        'provider_id' => 'integer',
        'service_id' => 'integer',
        'service_package_id' => 'integer',
        'service_price' => 'float',
        'tax' => 'float',
        'per_serviceman_charge' => 'float',
        'required_servicemen' => 'integer',
        'total_extra_servicemen' => 'integer',
        'total_servicemen' => 'integer',
        'platform_fees' => 'float',
        'address_id' => 'integer',
        'total_extra_servicemen_charge' => 'float',
        'subtotal' => 'float',
        'parent_id' => 'integer',
        'convert_wallet_balance' => 'double',
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_by_id = auth()->user()?->id ?? Helpers::getCurrentUserId();
        });
    }

    public function scopeWhereParentIdNull($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeWhereProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeWhereServiceman($query, $servicemanId)
    {
        return $query->whereHas('servicemen', function ($query) use ($servicemanId) {
            $query->where('users.id', $servicemanId);
        });
    }

    public static function getFilteredBookings($providerId = null, $servicemanId = null , $start_date = null, $end_date = null)
    {
        return self::when($providerId == null && $servicemanId == null, function ($query) {
            $query?->whereParentIdNull();
        })
        ->when($providerId, function ($query, $providerId) {
            $query?->whereProvider($providerId);
        })
        ->when($servicemanId, function ($query, $servicemanId) {
            $query->whereServiceman($servicemanId);
        })
        ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }, function ($query) {
            $query->whereDate('created_at', '<=', now());
        })
        ->with(['booking_status', 'service'])
        ->latest()
        ->get();

    }

    public static function countByStatus($bookings, $status,$start_date = null , $end_date = null)
    {
        $query =  $bookings->filter(function ($booking) use ($status) {
            return $booking->booking_status?->name === $status;
        });

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $query?->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }

    public static function getBookingStatusById($bookings, $userId, $status)
    {
        $role = Helpers::getRoleByUserId($userId);
        if($role == RoleEnum::PROVIDER)
        {
            return $bookings->filter(function ($booking) use ($userId, $status) {
                return $booking->provider_id === $userId && $booking->booking_status?->name === $status;
            })->count();
        } elseif ($role == RoleEnum::SERVICEMAN) {
            return $bookings->filter(function ($booking) use ($userId, $status) {

                $isServiceman = $booking->servicemen->contains('id', $userId);


                $isStatusMatched = $booking->booking_status?->name === $status;
                return $isServiceman && $isStatusMatched;
            })->count();
        }elseif($role == RoleEnum::CONSUMER)
        {
            return $bookings->filter(function ($booking) use ($userId, $status) {
                return $booking->consumer_id === $userId && $booking->booking_status?->name === $status;
            })->count();
        }
    }

    /**
     * @return int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('order')->id;
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id')->withTrashed();
    }

    public function coupon(): belongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id')->withTrashed();
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id')->withTrashed();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id')->withTrashed();
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function service_address(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'service_address_id');
    }

    public function booking_status(): HasOne
    {
        return $this->hasOne(BookingStatus::class, 'id', 'booking_status_id');
    }

    public function servicemen(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'booking_servicemen', 'booking_id', 'serviceman_id');
    }

    public function additional_services()
    {
        return $this->belongsToMany(Service::class, 'booking_additional_services', 'booking_id', 'additional_service_id')
                    ->withPivot('price', 'qty', 'total_price')
                    ->select('services.id as id', 'services.title')
                    ->withTimestamps();
    }

    public function sub_bookings()
    {
        return $this->hasMany(Booking::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Booking::class, 'parent_id');
    }

    public function booking_status_logs()
    {
        return $this->hasMany(BookingStatusLog::class, 'booking_id')?->latest();
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function bookingReasons()
    {
        return $this->hasMany(BookingReasonLog::class, 'booking_id')->with('status');
    }

    public function commission_history()
    {
        return $this->hasMany(CommissionHistory::class, 'booking_id');
    }

    public function serviceProofs()
    {
        return $this->hasMany(ServiceProof::class);
    }

    public function extra_charges()
    {
        return $this->hasMany(ExtraCharge::class);
    }

    public static function getAuthServicemanBookings()
    {
        return self::whereHas('servicemen', function ($query) {
            $query->where('users.id', auth()->id());
        })->whereNotNull('parent_id')->with(['booking_status', 'service', 'service.media', 'provider', 'consumer', 'serviceProofs', 'extra_charges'])
        ->latest()
        ->get();
    }

    public static function getTodayAuthServicemanBookings()
    {
        return self::whereHas('servicemen', function ($query) {
            $query->where('users.id', auth()->id());
        })->whereNotNull('parent_id')->whereDate('date_time', '=', now()->toDateString())->with(['booking_status', 'service', 'service.media', 'provider', 'consumer', 'serviceProofs', 'extra_charges'])
        ->latest()
        ->get();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Booking')
            ->setDescriptionForEvent(fn(string $eventName) => "{$this->booking_number} - Booking has been {$eventName}");
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'booking_tax')
                    ->withPivot(['rate', 'amount'])
                    ->withTimestamps();
    }

    public function getGrandTotalWithExtrasAttribute()
    {
        $extraChargeGrandTotal = $this->extra_charges->sum('grand_total');
        if($extraChargeGrandTotal > 0){
            return $this->total + $extraChargeGrandTotal;
        }
        return 0;
    }

    public function videoConsultation()
    {
        return $this->belongsTo(VideoConsultation::class);
    }
}
