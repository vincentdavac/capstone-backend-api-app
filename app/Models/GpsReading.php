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
        'altitude',
        'satellites',
        'recorded_at',
    ];

    /**
     * Enable automatic timestamps (created_at and updated_at)
     */
    public $timestamps = true;

    /**
     * Cast attributes to native types
     */
    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'altitude'    => 'float',
        'recorded_at' => 'datetime',
    ];

    /**
     * Relationship: Each GPS reading belongs to a specific buoy
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
