<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class recent_alerts extends Model
{
    protected $table = 'recent_alerts';
    use HasFactory;
    protected $fillable = [
        'alertId',
        'buoy_id',
        'description',
        'alert_level',
        'sensor_type',
        'recorded_at',
    ];
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
    public function buoys()
    {
        return $this->belongsTo(Buoy::class, 'buoy_id', 'id');
    }
     public function buoyid(){
        return $this->belongsTo(Buoy::class, 'buoy_id');
    }
}
