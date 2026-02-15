<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RainReadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'buoyId' => $this->buoy_id,

                'percentage' => $this->percentage,

                'recordedAt'   => $this->recorded_at?->toISOString(),
                'recordedDate' => $this->recorded_at?->format('F d, Y') ?? ' ',
                'recordedTime' => $this->recorded_at?->format('h:i:s A') ?? ' ',

                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',

                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],

            //  Included buoy (only when loaded)
            'buoy' => new BuoyResource(
                $this->whenLoaded('buoy')
            ),
        ];
    }
}
