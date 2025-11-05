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
                'image' => $this->image
                    ? config('app.url') . '/footer_images/' . $this->image
                    : null,
                'caption' => $this->caption,
                'documentationLink' => $this->documentation_link,
                'researchPaperLink' => $this->research_paper_link,
                'emailAddress' => $this->email_address,
                'facebookLink' => $this->facebook_link,
                'youtubeLink' => $this->youtube_link,
                'footerSubtitle' => $this->footer_subtitle,
                'isArchived' => (bool) $this->is_archived,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
