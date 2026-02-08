<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsReading extends Model
{
    use HasFactory;

    protected $table = 'gps_readings';

    protected $fillable = [
        'buoy_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];

    /**
     * Cast attributes to appropriate types
     */
    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'recorded_at' => 'datetime',
    ];

    /**
     * Relationship: A GPS reading belongs to a specific Buoy.
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class, 'buoy_id');
    }
}
