<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'alert_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alert()
    {
        return $this->belongsTo(alerts::class);
    }
}
