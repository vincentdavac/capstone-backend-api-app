<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageFooter extends Model
{
    protected $fillable = [
        'image',
        'caption',
        'documentation_link',
        'research_paper_link',
        'email_address',
        'facebook_link',
        'youtube_link',
        'footer_subtitle',
        'is_archived',
    ];
}
