<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemNotifications extends Model
{
    use HasFactory;

    protected $table = 'system_notifications';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'barangay_id',
        'receiver_role',
        'title',
        'body',
        'status',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Sender (Admin / User)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Receiver (User)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Barangay relationship
    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}
