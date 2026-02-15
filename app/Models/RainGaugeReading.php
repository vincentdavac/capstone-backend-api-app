<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RainGaugeReading extends Model
{
    use HasFactory;

    protected $table = 'rain_gauge_readings';

    protected $fillable = [
        'buoy_id',
        'rainfall_mm',
        'tip_count',
        'recorded_at',
        'updated_at', // optional, allow mass-assignment if needed
    ];

    public $timestamps = true; // created_at and updated_at managed automatically


    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];


    /**
     * Relationship: a rain gauge reading belongs to a buoy
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
