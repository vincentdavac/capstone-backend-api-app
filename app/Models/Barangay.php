<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'barangays';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'barangay_code',
        'name',
        'number',
        'river_wall_height',
        'square_meter',
        'hectare',
        'white_level_alert',
        'blue_level_alert',
        'red_level_alert',
        'description',
        'attachment',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'barangay_code' => 'string',
        'number' => 'integer',
        'river_wall_height' => 'double',
        'square_meter' => 'double',
        'hectare' => 'double',
        'white_level_alert' => 'double',
        'blue_level_alert' => 'double',
    ];

    /**
     * Automatically generate a barangay_code if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->barangay_code)) {
                $year = date('Y');
                $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $model->barangay_code = "BRGY-{$year}-{$random}";
            }
        });
    }


    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Add this relationship
    public function buoys()
    {
        return $this->hasMany(Buoy::class, 'barangay_id', 'id');
    }
}
