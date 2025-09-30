<?php

namespace App\Models;

use App\Models\ModerationFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ThreadMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'thread_id',
        'author_id',
        'body',
        'attachments',
        'meta',
        'is_system',
        'delivered_at',
    ];

    protected $casts = [
        'thread_id' => 'integer',
        'author_id' => 'integer',
        'attachments' => 'array',
        'meta' => 'array',
        'is_system' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ThreadMessage $message): void {
            if (! $message->public_id) {
                $message->public_id = (string) Str::ulid();
            }
        });
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function moderationFlags(): MorphMany
    {
        return $this->morphMany(ModerationFlag::class, 'flaggable');
    }
}
