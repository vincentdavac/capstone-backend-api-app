<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaterTemperatureReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buoy_id' => $this->buoy_id,
            'temperature_celsius' => $this->temperature_celsius,
            'temperature_fahrenheit' => $this->temperature_fahrenheit,
            'report_status' => $this->report_status,
            'recorded_at' => $this->recorded_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
