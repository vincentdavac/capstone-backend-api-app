<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrototypeFile extends Model
{
    use HasFactory;

    protected $table = 'homepage_prototype_file';

    protected $fillable = [
        'name',
        'attachment',
        'is_archived',
    ];
}
