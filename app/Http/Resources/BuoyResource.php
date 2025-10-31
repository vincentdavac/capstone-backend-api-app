<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuoyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'buoyCode' => $this->buoy_code,
                'riverName' => $this->river_name,
                'wallHeight' => $this->wall_height,
                'riverHectare' => $this->river_hectare,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'barangay' => $this->barangay,
                'attachment' => $this->attachment
                    ? config('app.url') . '/buoy_attachment/' . $this->attachment
                    : null,
                'status' => $this->status,
                'maintenanceAt' => $this->maintenance_at,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
