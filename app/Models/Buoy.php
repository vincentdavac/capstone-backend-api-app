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

    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // GPS readings
    public function gpsReadings()
    {
        return $this->hasMany(GpsReading::class, 'buoy_id');
    }

    public function latestGpsReading()
    {
        return $this->hasOne(GpsReading::class, 'buoy_id')
            ->latestOfMany('recorded_at');
    }

    // Battery health
    public function batteryHealth()
    {
        return $this->hasMany(BatteryHealth::class, 'buoy_id');
    }

    public function latestBatteryHealth()
    {
        return $this->hasOne(BatteryHealth::class, 'buoy_id')
            ->latestOfMany('recorded_at');
    }

    // Relay status
    public function relayStatuses()
    {
        return $this->hasMany(RelayStatus::class, 'buoy_id');
    }

    public function latestRelayStatus()
    {
        return $this->hasOne(RelayStatus::class, 'buoy_id')
            ->latestOfMany('recorded_at');
    }

    // Barangay
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function alerts()
    {
        return $this->hasMany(recent_alerts::class, 'buoy_id', 'id');
    }

    // Rain gauge
    public function rainGaugeReadings()
    {
        return $this->hasMany(RainGaugeReading::class, 'buoy_id');
    }

    public function latestRainGaugeReading()
    {
        return $this->hasOne(RainGaugeReading::class, 'buoy_id')
            ->latestOfMany('recorded_at');
    }

    // Rain sensor
    public function rainSensorReadings()
    {
        return $this->hasMany(RainSensorReading::class, 'buoy_id');
    }

    // Wind
    public function windReadings()
    {
        return $this->hasMany(WindReading::class, 'buoy_id');
    }

    public function latestWindReading()
    {
        return $this->hasOne(WindReading::class, 'buoy_id')
            ->latestOfMany('recorded_at');
    }
}
