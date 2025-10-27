<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RainGaugeReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_id',
        'rainfall_mm',
        'tip_count',
        'report_status',
        'recorded_at',
    ];

    public $timestamps = false;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
