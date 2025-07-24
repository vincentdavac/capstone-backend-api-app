<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageTeamResource extends JsonResource
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
                'name' => $this->name,
                'role' => $this->role,
                'image' => $this->image,
                'imageUrl' => $this->image_url,
                'facebookLink' => $this->facebook_link,
                'twitterLink' => $this->twitter_link,
                'linkedinLink' => $this->linkedin_link,
                'instagramLink' => $this->instagram_link,
                'isActive' => (bool) $this->is_active,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
