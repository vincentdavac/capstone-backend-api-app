<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageFeedback extends Model
{
    use HasFactory;

    protected $table = 'homepage_feedbacks';

    protected $fillable = [
        'user_id',
        'rate',
        'feedback',
        'is_archived',
    ];

    /**
     * Relationship: Feedback belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
