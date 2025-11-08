<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelayStatus extends Model
{
    use HasFactory;

    protected $table = 'relay_status';

    protected $fillable = [
        'buoy_id',
        'relay_state',
    ];

    /**
     * Enable automatic timestamps (created_at and updated_at)
     */
    public $timestamps = true;

    /**
     * Cast attributes to correct data types
     */
    protected $casts = [
        'relay_state' => 'boolean',
    ];

    /**
     * Relationship: Each relay status belongs to a specific buoy
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class, 'buoy_id');
    }
}
