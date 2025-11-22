<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageAboutCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'homepageAboutId' => $this->homepage_about_id,
                'cardTitle' => $this->card_title,
                'cardDescription' => $this->card_description,
                'isArchive' => (bool) $this->is_archive,
                'createdDate' => $this->created_at?->format('F d, Y') ?? null,
                'createdTime' => $this->created_at?->format('h:i:s A') ?? null,
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? null,
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? null,
            ],
        ];
    }
}
