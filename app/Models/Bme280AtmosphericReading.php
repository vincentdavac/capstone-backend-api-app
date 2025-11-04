<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bme280AtmosphericReading extends Model
{
    use HasFactory;

    protected $table = 'bme280_atmospheric_readings';

    protected $fillable = [
        'buoy_id',
        'pressure_mbar',
        'pressure_hpa',
        'altitude',
        'recorded_at',
    ];

    // Disable Laravel's automatic timestamps since recorded_at handles timing
    public $timestamps = true;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
