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
        'recorded_at',
    ];

    protected $casts = [
        'temperature_celsius' => 'decimal:2',
        'temperature_fahrenheit' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
