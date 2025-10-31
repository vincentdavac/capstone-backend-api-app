<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WindReading extends Model
{
    use HasFactory;

    protected $table = 'wind_readings';

    protected $fillable = [
        'buoy_id',
        'wind_speed_m_s',
        'wind_speed_k_h',
        'report_status',
        'recorded_at',
        'updated_at',
    ];

    public $timestamps = true; // since 'updated_at' is managed in the table

    /**
     * Relationship: A wind reading belongs to a buoy.
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
