<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bme280HumidityReading extends Model
{
    use HasFactory;

    protected $table = 'bme280_humidity_readings';

    protected $fillable = [
        'buoy_id',
        'humidity',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
