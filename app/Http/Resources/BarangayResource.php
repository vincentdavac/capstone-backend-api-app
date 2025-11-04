<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangayResource extends JsonResource
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
                'barangayCode' => $this->barangay_code,
                'name' => $this->name,
                'number' => $this->number,
                'riverWallHeight' => $this->river_wall_height,
                'squareMeter' => $this->square_meter,
                'hectare' => $this->hectare,
                'whiteLevelAlert' => $this->white_level_alert,
                'blueLevelAlert' => $this->blue_level_alert,
                'redLevelAlert' => $this->red_level_alert,
                'description' => $this->description,
                'attachment' => $this->attachment
                    ? config('app.url') . '/barangay_attachment/' . $this->attachment
                    : null,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
