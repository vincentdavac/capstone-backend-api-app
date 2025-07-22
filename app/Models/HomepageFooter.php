<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageFooter extends Model
{
    protected $fillable = [
        'footer_text',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'is_active',
    ];
}
