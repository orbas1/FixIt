<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicemanCommissions extends Model
{
    use HasFactory;

    protected $table = 'serviceman_commissions';

    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_history_id',
        'serviceman_id',
        'commission',
    ];

    protected $casts = [
        'commission_history_id' => 'integer',
        'serviceman_id' => 'integer',
        'commission' => 'float',
    ];

    public function serviceman()
    {
        return $this->belongsTo(User::class, 'serviceman_id');
    }

    public static function getAuthServicemanCommissions(): float
    {
        return self::where('serviceman_id', auth()->id())->sum('commission');
    }
}
