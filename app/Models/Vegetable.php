<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Vegetable extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
