<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSlider extends Model
{
    protected $fillable = [
        'image',
        'image_url',
        'title',
        'description',
        'is_active',
    ];

    public $timestamps = true;
}
