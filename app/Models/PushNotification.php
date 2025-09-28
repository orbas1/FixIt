<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PushNotification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by = auth()->user()->id;
        });
    }

    protected $fillable = [
        'title',
        'message',
        'send_to',
        'user_id',
        'service_id',
        'is_read',
        'image_url',
        'url',
        'notification_type',
    ];

    // public $with = [
    //     'media',
    // ];

    protected $casts = [
        'user_id' => 'integer',
        'service_id' => 'integer',
        'is_read' => 'integer',
    ];
}
