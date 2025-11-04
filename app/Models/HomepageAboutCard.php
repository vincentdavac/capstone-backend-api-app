<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageAboutCard extends Model
{
    protected $fillable = ['homepage_about_id', 'card_title', 'card_description'];

    public function about()
    {
        return $this->belongsTo(HomepageAbout::class);
    }
}
