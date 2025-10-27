<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuoyStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'buoyId' => $this->buoy_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'batteryHealth' => $this->battery_health,
                'alert' => $this->alert,
                'recordedAt' => $this->recorded_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
