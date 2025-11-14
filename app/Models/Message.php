<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    // Fillable fields for mass assignment
    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'attachment',
        'is_read',
    ];

    /**
     * The chat this message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    /**
     * The sender of the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
