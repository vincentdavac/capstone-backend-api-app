<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bme280HumidityReading extends Model
{
    use HasFactory;

    protected $table = 'bme280_humidity_readings';

    protected $fillable = [
        'buoy_id',
        'humidity',
        'recorded_at',
        'updated_at',
    ];

    // Enable timestamps since you have `updated_at`
    public $timestamps = true;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
