<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class alerts extends Model
{
    use HasFactory;
    protected $fillable = [
        'alert_id',
        'broadcast_by',
        'is_read',
        'recorded_at',
    ];

    public function recentAlert()
    {
        return $this->belongsTo(recent_alerts::class, 'alert_id');
    }
}
