<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageAboutResource extends JsonResource
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
                'title' => $this->title,
                'caption' => $this->caption,
                'image' => $this->image,
                'imageUrl' => $this->image_url,
                'sideTitle' => $this->side_title,
                'sideDescription' => $this->side_description,
                'firstCardTitle' => $this->first_card_title,
                'firstCardDescription' => $this->first_card_description,
                'secondCardTitle' => $this->second_card_title,
                'secondCardDescription' => $this->second_card_description,
                'thirdCardTitle' => $this->third_card_title,
                'thirdCardDescription' => $this->third_card_description,
                'isActive' => (bool) $this->is_active,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
