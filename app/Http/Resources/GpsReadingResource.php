<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpsReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buoy_id' => $this->buoy_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'altitude' => $this->altitude,
            'satellites' => $this->satellites,
            'report_status' => $this->report_status,
            'recorded_at' => $this->recorded_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
