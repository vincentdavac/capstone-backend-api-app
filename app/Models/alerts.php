<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class alerts extends Model{
    use HasFactory;

    protected $fillable = [
        'alertId',
        'description',
        'alert_level',
        'sensor_type',
        'recorded_at',
    ];
}
