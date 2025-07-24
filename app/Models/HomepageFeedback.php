<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageFeedback extends Model
{
        protected $table = 'homepage_feedbacks'; // 👈 Add this line

    protected $fillable = [
        'name',
        'role',
        'image',
        'image_url',
        'rate',
        'feedback',
        'is_active',
    ];
}
