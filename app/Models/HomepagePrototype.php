<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepagePrototype extends Model
{
    protected $fillable = [
        'title',
        'caption',
        'image',
        'image_url',
        'is_active',
    ];
}
