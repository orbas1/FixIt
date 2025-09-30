<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'engine',
        'verdict',
        'report',
        'sha256',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'report' => 'array',
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
