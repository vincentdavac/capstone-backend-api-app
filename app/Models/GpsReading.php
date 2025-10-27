<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'latitude',
        'longitude',
        'altitude',
        'satellites',
        'report_status',
        'recorded_at',
    ];

    // Optional: If you want timestamps handled automatically
    public $timestamps = false;

    // Optional relationship
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
