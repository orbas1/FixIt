<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateApp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rating';

    protected $fillable = [
        'rating',
        'description',
        'name',
        'email',
        'consumer_id',
        'error_type',
    ];

    // protected $with = ['user'];

    protected $casts = [
        'consumer_id' => 'integer',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'consumer_id', 'id');
    }
}
