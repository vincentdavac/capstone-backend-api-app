<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mpu6050Reading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'accel_x',
        'accel_y',
        'accel_z',
        'gyro_x',
        'gyro_y',
        'gyro_z',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
