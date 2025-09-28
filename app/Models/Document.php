<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'status',
        'is_required',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_at'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('status', 'notes', 'identity_no');
    }
}
