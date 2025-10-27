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
                'locationName' => $this->location_name,
                'status' => $this->status,
                'isActive' => (bool) $this->is_active,
                'installedAt' => $this->installed_at,
                'maintenanceAt' => $this->maintenance_at,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
