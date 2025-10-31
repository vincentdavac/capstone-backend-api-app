<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatteryHealth extends Model
{
    use HasFactory;

    protected $table = 'battery_health';

    protected $fillable = [
        'buoy_id',
        'percentage',
        'voltage',
        'recorded_at',
    ];

    /**
     * Enable timestamps for created_at and updated_at
     */
    public $timestamps = true;

    /**
     * Cast attributes to appropriate types
     */
    protected $casts = [
        'percentage'  => 'float',
        'voltage'     => 'float',
        'recorded_at' => 'float',
    ];



    /**
     * Relationship: each battery health record belongs to a buoy
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
