<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RainSensorReading extends Model
{
    use HasFactory;

    protected $table = 'rain_sensor_readings';

    protected $fillable = [
        'buoy_id',
        'percentage',
        'recorded_at',
    ];

    /**
     * Enable created_at and updated_at
     */
    public $timestamps = true;

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'percentage'  => 'float',
        'recorded_at' => 'datetime',
    ];

    /**
     * Relationship: A rain sensor reading belongs to a buoy.
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
