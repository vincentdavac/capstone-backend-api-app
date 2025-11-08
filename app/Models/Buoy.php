<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buoy extends Model
{
    use HasFactory;
    protected $fillable = [
        'buoy_code',
        'river_name',
        'wall_height',
        'river_hectare',
        'latitude',
        'longitude',
        'barangay_id',
        'attachment',
        'status',
        'maintenance_at',
    ];

    public $timestamps = true; // Keeps created_at and updated_at timestamps


    public function gpsReadings()
    {
        return $this->hasMany(GpsReading::class, 'buoy_id');
    }

    public function batteryHealth()
    {
        return $this->hasMany(BatteryHealth::class, 'buoy_id');
    }

    public function relayStatus()
    {
        return $this->hasMany(RelayStatus::class, 'buoy_id');
    }

    // Relationship: A buoy belongs to one barangay
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'id');
    }

    public function bme280_atmospheric_readings()
    {
        return $this->hasmany(Bme280AtmosphericReading::class, 'buoy_id', 'id');
    }

    public function Bme280TemperatureReading()
    {
        return $this->hasMany(Bme280TemperatureReading::class, 'buoy_id', 'id');
    }
    public function DepthReading()
    {
        return $this->hasMany(DepthReading::class, 'buoy_id', 'id');
    }
    public function RainGaugeReading()
    {
        return $this->hasMany(RainGaugeReading::class, 'buoy_id', 'id');
    }
    public function RainSensorReading()
    {
        return $this->hasMany(RainSensorReading::class, 'buoy_id', 'id');
    }
    public function WaterTemperatureReading()
    {
        return $this->hasMany(WaterTemperatureReading::class, 'buoy_id', 'id');
    }
    public function WindReading()
    {
        return $this->hasMany(WindReading::class, 'buoy_id', 'id');
    }
    public function Bme280HumidityReading()
    {
        return $this->hasMany(Bme280HumidityReading::class, 'buoy_id', 'id');
    }
}
