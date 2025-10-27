<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Bme280HumidityReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buoy_id' => $this->buoy_id,
            'humidity' => $this->humidity,
            'report_status' => $this->report_status,
            'recorded_at' => $this->recorded_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
