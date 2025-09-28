<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Backup extends Model
{

    use HasFactory,  LogsActivity ,SoftDeletes ;
    protected $table = "backup_logs";

    protected $fillable = [
       'title',
       'description',
       'file_path',
    ];

   protected $casts = [
        'file_path' => 'json'
   ];

   public function getActivitylogOptions(): LogOptions
   {
       return LogOptions::defaults()
           ->logAll()
           ->useLogName('Backup')
           ->setDescriptionForEvent(fn(string $eventName) => "Backup File has been {$eventName}");
   }
}
