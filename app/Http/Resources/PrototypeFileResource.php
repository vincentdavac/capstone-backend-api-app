<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrototypeFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'attachment' => $this->attachment
                    ? config('app.url') . '/prototype_files/' . $this->attachment
                    : null,
                'isArchived' => $this->is_archived,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
