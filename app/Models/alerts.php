<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class alerts extends Model
{
    use HasFactory;
    protected $table = 'alerts';
    protected $fillable = [
        'alert_id',
        'broadcast_by',
        'user_id',
        'is_read',
        'recorded_at',
    ];

    public function recentAlert(){
        return $this->belongsTo(recent_alerts::class, 'alert_id');
    }
    public function prototypes(){
        return $this->hasManyThrough( Buoy::class,recent_alerts::class,'alert_id','id', 'id', 'barangay_id');
    }
}
