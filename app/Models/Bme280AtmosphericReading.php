<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bme280AtmosphericReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'pressure_mbar',
        'pressure_hpa',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
