<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buoy extends Model
{
    use HasFactory;

    protected $fillable = [
        'buoy_code',
        'river_name',
        'wall_height',
        'river_hectare',
        'latitude',
        'longitude',
        'barangay',
        'attachment',
        'status',
        'maintenance_at',
    ];

    public $timestamps = true; // Keeps created_at and updated_at timestamps
}
