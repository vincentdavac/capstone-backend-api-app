<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelayStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buoyId' => $this->buoy_id,
            'relayState' => $this->relay_state,
            // Formatted date and time
            'createdDate' => $this->created_at?->format('F d, Y') ?? null,
            'createdTime' => $this->created_at?->format('h:i:s A') ?? null,
            'updatedDate' => $this->updated_at?->format('F d, Y') ?? null,
            'updatedTime' => $this->updated_at?->format('h:i:s A') ?? null,
        ];
    }
}
