<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAuditReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool',
        'status',
        'issues_found',
        'summary',
        'executed_by',
        'executed_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'executed_at' => 'datetime',
    ];
}
