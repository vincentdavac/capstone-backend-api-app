<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageFooterResource extends JsonResource
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
                'footerText' => $this->footer_text,
                'facebookUrl' => $this->facebook_url,
                'twitterUrl' => $this->twitter_url,
                'instagramUrl' => $this->instagram_url,
                'linkedinUrl' => $this->linkedin_url,
                'isActive' => (bool) $this->is_active,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
