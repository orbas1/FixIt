<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BankDetail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'bank_name',
        'holder_name',
        'account_number',
        'branch_name',
        'ifsc_code',
        'swift_code',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'account_number' => 'integer',
    ];

    // protected $with = [
    //     'media',
    // ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
