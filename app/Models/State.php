<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    /**
     * The States that are mass assignable.
     *
     * @var array<int, string>
     */
    public $fillable = [
        'name',
        'country_id',
    ];

    protected $casts = [
        'country_id' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function address(): HasMany
    {
        return $this->hasMany(Address::class, 'state_id');
    }
}
