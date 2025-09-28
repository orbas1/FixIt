<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'serviceman_id',
        'time_slots',
        'is_active'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'time_slots' => 'array',
        'provider_id' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $with = [
        'provider',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
}
