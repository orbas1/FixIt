<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DataDomain extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'description',
        'data_categories',
        'is_active',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $domain): void {
            if (empty($domain->ulid)) {
                $domain->ulid = (string) Str::ulid();
            }

            if (empty($domain->slug)) {
                $domain->slug = Str::slug($domain->name);
            }
        });
    }

    public function assets(): HasMany
    {
        return $this->hasMany(DataAsset::class);
    }
}
