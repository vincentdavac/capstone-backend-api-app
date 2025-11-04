<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepagePrototype extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'position',     // accepts only 'left' or 'right'
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];
}
