<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Review extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * The Review that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rating',
        'service_id',
        'provider_id',
        'consumer_id',
        'serviceman_id',
        'description',
    ];

    // protected $with = [
    //     'media',
    //     'consumer',
    // ];

    protected $casts = [
        'provider_id' => 'integer',
        'serviceman_id' => 'integer',
        'consumer_id' => 'integer',
        'service_id' => 'integer',
        'rating' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $user = auth()->user();

            if ($user && $user->hasRole(RoleEnum::CONSUMER)) {
                $model->consumer_id = $user->id;
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }
}
