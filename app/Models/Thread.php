<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'service_request_id',
        'booking_id',
        'type',
        'status',
        'subject',
        'opened_by_id',
        'last_message_id',
        'last_message_at',
    ];

    protected $casts = [
        'service_request_id' => 'integer',
        'booking_id' => 'integer',
        'last_message_id' => 'integer',
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Thread $thread): void {
            if (! $thread->public_id) {
                $thread->public_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ThreadParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('is_active', true);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ThreadMessage::class)->latestOfMany();
    }
}
