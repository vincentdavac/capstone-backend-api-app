<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WindReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'wind_speed_m_s',
        'wind_speed_k_h',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
