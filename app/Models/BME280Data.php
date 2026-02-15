<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BME280Data extends Model
{
    use HasFactory;

    protected $table = 'bme280_data';

    protected $fillable = [
        'buoy_id',
        'temperature_celsius',
        'temperature_fahrenheit',
        'humidity',
        'pressure_mbar',
        'pressure_hpa',
        'altitude',
        'recorded_at',
    ];

    protected $casts = [
        'temperature_celsius' => 'float',
        'temperature_fahrenheit' => 'float',
        'humidity' => 'float',
        'pressure_mbar' => 'float',
        'pressure_hpa' => 'float',
        'altitude' => 'float',
        'recorded_at' => 'datetime',
    ];

    /**
     * Optional relationship (if you have a Buoy model)
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
