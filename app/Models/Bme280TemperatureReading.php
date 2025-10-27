<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bme280TemperatureReading extends Model
{
    use HasFactory;

    protected $table = 'bme280_temperature_readings';

    protected $fillable = [
        'buoy_id',
        'temperature_celsius',
        'temperature_fahrenheit',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
