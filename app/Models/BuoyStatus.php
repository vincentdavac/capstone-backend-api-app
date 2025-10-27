<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuoyStatus extends Model
{
    protected $table = 'buoy_status';

    protected $fillable = [
        'buoy_id',
        'latitude',
        'longitude',
        'battery_health',
        'alert',
        'recorded_at',
    ];

    public $timestamps = false; // ✅ disable timestamps

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
