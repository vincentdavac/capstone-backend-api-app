<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotlines extends Model
{
    use HasFactory;

    protected $table = 'hotlines';

    protected $fillable = [
        'created_by_role',
        'barangay_id',
        'number',
        'description',
        'is_archived',
    ];
    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }
}
