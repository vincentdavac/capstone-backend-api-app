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
        'recorded_at',
    ];

    // Enable timestamps since your table includes created_at and updated_at
    public $timestamps = true;

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
