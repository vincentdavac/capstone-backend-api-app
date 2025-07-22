<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageFeedback extends Model
{
    protected $fillable = [
        'name',
        'role',
        'image',
        'image_link',
        'rate',
        'feedback',
        'is_active',
    ];
}
