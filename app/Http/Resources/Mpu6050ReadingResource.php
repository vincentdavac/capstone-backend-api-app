<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Mpu6050ReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buoy_id' => $this->buoy_id,
            'accel_x' => $this->accel_x,
            'accel_y' => $this->accel_y,
            'accel_z' => $this->accel_z,
            'gyro_x'  => $this->gyro_x,
            'gyro_y'  => $this->gyro_y,
            'gyro_z'  => $this->gyro_z,
            'report_status' => $this->report_status,
            'recorded_at' => $this->recorded_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
