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

            'attributes' => [
                'buoyId'     => $this->buoy_id,
                'relayState' => $this->relay_state,
                'recordedAt' => $this->recorded_at?->toISOString(),
                'recordedDate' => $this->recorded_at?->format('F d, Y') ?? ' ',
                'recordedTime' => $this->recorded_at?->format('h:i:s A') ?? ' ',

                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',

                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],

            // Include buoy using BuoyResource (only when loaded)
            'buoy' => new BuoyResource($this->whenLoaded('buoy')),

            // Include user who triggered (only when loaded)
            'triggeredBy' => $this->whenLoaded('triggeredBy', function () {
                return $this->triggeredBy ? [
                    'id'        => $this->triggeredBy->id,
                    'firstName' => $this->triggeredBy->first_name,
                    'lastName'  => $this->triggeredBy->last_name,
                    'email'     => $this->triggeredBy->email,
                ] : null;
            }),
        ];
    }
}
