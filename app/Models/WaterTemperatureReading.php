<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterTemperatureReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'temperature_celsius',
        'temperature_fahrenheit',
        'recorded_at',
        'updated_at',
    ];

    public $timestamps = true; // because you have created_at and updated_at columns

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
