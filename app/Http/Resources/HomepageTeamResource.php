<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'userId' => $this->user_id,
                'userName' => $this->user
                    ? ($this->user->first_name . ' ' . $this->user->last_name)
                    : 'N/A',
                'role' => $this->role,
                'image' => $this->image
                    ? config('app.url') . '/homepage_team_images/' . $this->image
                    : null,
                'facebookLink' => $this->facebook_link,
                'twitterLink' => $this->twitter_link,
                'linkedinLink' => $this->linkedin_link,
                'instagramLink' => $this->instagram_link,
                'isArchived' => (bool) $this->is_archived,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
