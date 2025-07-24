<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageAbout extends Model
{
    protected $fillable = [
        'title',
        'caption',
        'image',
        'image_url',
        'side_title',
        'side_description',
        'first_card_title',
        'first_card_description',
        'second_card_title',
        'second_card_description',
        'third_card_title',
        'third_card_description',
    ];

    public $timestamps = true; // This enables created_at and updated_at
}
