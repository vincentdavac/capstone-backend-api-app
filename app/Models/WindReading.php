<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WindReading extends Model
{
    use HasFactory;

    protected $table = 'wind_readings';

    protected $fillable = [
        'buoy_id',
        'wind_speed_m_s',
        'wind_speed_k_h',
        'recorded_at',   // Sensor-provided timestamp
    ];

    public $timestamps = true; // Uses created_at and updated_at automatically

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Relationship: A wind reading belongs to a buoy.
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
