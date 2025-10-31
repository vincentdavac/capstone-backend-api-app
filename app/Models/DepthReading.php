<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepthReading extends Model
{
    use HasFactory;

    protected $table = 'depth_readings';

    protected $fillable = [
        'buoy_id',
        'pressure_mbar',
        'pressure_hpa',
        'depth_m',
        'depth_ft',
        'water_altitude',
        'report_status',
        'recorded_at',
    ];

    // Use timestamps for created_at and updated_at
    public $timestamps = true;

    /**
     * Define relationship with the Buoy model.
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
