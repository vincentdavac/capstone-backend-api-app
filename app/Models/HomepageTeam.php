<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'role',
        'image',
        'facebook_link',
        'twitter_link',
        'linkedin_link',
        'instagram_link',
        'is_archived',
    ];

    /**
     * Cast attributes to native types.
     */
    protected $casts = [
        'is_archived' => 'boolean',
    ];
}
