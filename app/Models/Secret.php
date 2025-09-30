<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Secret extends Model
{
    use HasFactory;

    protected $table = 'secrets_vault';

    protected $fillable = [
        'key',
        'version',
        'value',
        'metadata',
        'rotated_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'rotated_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Crypt::decryptString($value),
            set: function (?string $value) {
                if ($value === null) {
                    return null;
                }

                $payload = is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : (string) $value;

                return Crypt::encryptString($payload);
            }
        );
    }

    protected static function booted(): void
    {
        static::creating(function (Secret $secret) {
            if (empty($secret->version)) {
                $secret->version = Str::uuid()->toString();
            }
        });
    }
}
