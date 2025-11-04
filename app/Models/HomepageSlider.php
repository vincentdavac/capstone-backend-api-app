<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSlider extends Model
{
    protected $fillable = [
        'image',
        'title',
        'description',
        'is_archive',
    ];

    public $timestamps = true;
}
