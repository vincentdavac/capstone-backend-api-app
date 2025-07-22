<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageTeam extends Model
{
    protected $fillable = [
        'name',
        'position',
        'image',
        'image_link',
        'facebook_link',
        'twitter_link',
        'linkedin_link',
        'instagram_link',
        'is_active',
    ];
}
