<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'image',
        'facebook_link',
        'twitter_link',
        'linkedin_link',
        'instagram_link',
        'is_archived',
    ];

    /**
     * Relationship: Each HomepageTeam entry belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
