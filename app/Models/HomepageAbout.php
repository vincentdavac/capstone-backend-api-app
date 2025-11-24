<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageAbout extends Model
{
    protected $fillable = [
        'title',
        'caption',
        'image',
        'is_archived',
        'side_title',
        'side_description',
        'video_link',
    ];

    public $timestamps = true; // This enables created_at and updated_at


    public function cards()
    {
        return $this->hasMany(HomepageAboutCard::class);
    }
}
