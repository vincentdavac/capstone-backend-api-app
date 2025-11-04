<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageAboutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'caption' => $this->caption,
                'image' => $this->image
                    ? config('app.url') . '/homepage_about_image/' . $this->image
                    : null,
                'sideTitle' => $this->side_title,
                'sideDescription' => $this->side_description,
                'isArchived' => (bool) $this->is_archived,

                'createdDate' => $this->created_at?->format('F d, Y') ?? null,
                'createdTime' => $this->created_at?->format('h:i:s A') ?? null,
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? null,
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? null,
            ],

            // Include cards relationship
            'cards' => HomepageAboutCardResource::collection($this->whenLoaded('cards')),
        ];
    }
}
