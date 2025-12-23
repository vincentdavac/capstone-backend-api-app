<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotlinesResource extends JsonResource
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
                'barangayId'  => $this->barangay_id,
                'number'      => $this->number,
                'description' => $this->description,
                'isArchived'   => (bool) $this->is_archived,

                'createdDate' => $this->created_at?->format('F d, Y') ?? null,
                'createdTime' => $this->created_at?->format('h:i:s A') ?? null,
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? null,
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? null,
            ],

            // Include barangay info if loaded
            'barangay' => $this->whenLoaded('barangay', function () {
                return [
                    'id'   => $this->barangay->id,
                    'name' => $this->barangay->name,
                ];
            }),
        ];
    }
}
