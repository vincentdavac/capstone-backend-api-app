<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buoy extends Model
{
    protected $fillable = [
        'buoy_code',
        'location_name',
        'status',
        'is_active',
        'installed_at',
        'maintenance_at',
    ];

    public $timestamps = true; // Enables created_at and updated_at

    // ✅ Relationship: A buoy can have many status records
    public function statuses()
    {
        return $this->hasMany(BuoyStatus::class);
    }
}
