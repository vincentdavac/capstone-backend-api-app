<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelayStatus extends Model
{
    use HasFactory;

    protected $table = 'relay_status';

    // Allow mass assignment for these fields
    protected $fillable = [
        'buoy_id',
        'relay_state',
        'triggered_by',
        'recorded_at',
    ];

    /**
     * Enable automatic timestamps (created_at and updated_at)
     */
    public $timestamps = true;

    /**
     * Relationship: Each relay status belongs to a specific buoy
     */
    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }

    /**
     * Relationship: The admin/user who triggered this relay
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
