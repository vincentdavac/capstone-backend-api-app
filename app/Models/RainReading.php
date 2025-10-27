<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RainReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'is_raining',
        'analog_value',
        'percentage',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
