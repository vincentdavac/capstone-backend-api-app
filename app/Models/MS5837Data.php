<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MS5837Data extends Model
{
    use HasFactory;

    protected $table = 'ms5837_data';

    protected $fillable = [
        'buoy_id',

        'temperature_celsius',
        'temperature_fahrenheit',

        'depth_m',
        'depth_ft',

        'water_altitude',
        'water_pressure',

        'recorded_at',
    ];

    // Table has created_at & updated_at
    public $timestamps = true;

    /**
     * Relationship: MS5837 data belongs to a buoy
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }

    /**
     * Mutator to automatically compute Fahrenheit when Celsius is set
     */
    public function setTemperatureCelsiusAttribute($value)
    {
        $this->attributes['temperature_celsius'] = $value;
        $this->attributes['temperature_fahrenheit'] = round(($value * 9 / 5) + 32, 2);
    }

    /**
     * Boot method to automatically set recorded_at when creating
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->recorded_at) {
                $model->recorded_at = now();
            }
        });
    }
}
