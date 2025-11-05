<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageFaq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'is_archived',
    ];

    public $timestamps = true; // enable created_at and updated_at
}
