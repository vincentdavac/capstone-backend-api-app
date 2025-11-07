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

            // Include Barangay details
            'barangay' => $this->whenLoaded('barangay', function () {
                return [
                    'id' => $this->barangay->id,
                    'barangayCode' => $this->barangay->barangay_code,
                    'name' => $this->barangay->name,
                    'number' => $this->barangay->number,
                    'riverWallHeight' => $this->barangay->river_wall_height,
                    'squareMeter' => $this->barangay->square_meter,
                    'hectare' => $this->barangay->hectare,
                    'whiteLevelAlert' => $this->barangay->white_level_alert,
                    'blueLevelAlert' => $this->barangay->blue_level_alert,
                    'redLevelAlert' => $this->barangay->red_level_alert,
                    'description' => $this->barangay->description,
                    'attachment' => $this->barangay->attachment
                        ? config('app.url') . '/barangay_attachment/' . $this->barangay->attachment
                        : null,
                ];
            }),
        ];
    }
}
