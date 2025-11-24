<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageAboutCard extends Model
{
    protected $fillable = [
        'homepage_about_id',
        'card_title',
        'card_description',
        'is_archive',
    ];

    protected $casts = [
        'is_archive' => 'boolean',
    ];
    public function about()
    {
        return $this->belongsTo(HomepageAbout::class);
    }
}
