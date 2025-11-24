<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'userName' => $this->user
                    ? ($this->user->first_name . ' ' . $this->user->last_name)
                    : 'N/A',
                'userImage' => $this->user && $this->user->image
                    ? config('app.url') . '/profile_images/' . $this->user->image
                    : null,
                'rate' => $this->rate,
                'feedback' => $this->feedback,
                'isArchived' => (bool) $this->is_archived,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
