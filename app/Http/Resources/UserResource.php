<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'firstName' => $this->first_name,
                'lastName' => $this->last_name,
                'email' => $this->email,
                'contactNumber' => $this->contact_number,
                'houseNo' => $this->house_no,
                'street' => $this->street,
                'barangay' => $this->barangay,
                'municipality' => $this->municipality,
                'isAdmin' => (bool) $this->is_admin,
                // ✅ Always build image URL dynamically from filename
                'image' => $this->image ? config('app.url') . '/profile_images/' . $this->image : null,
                'imageUrl' => $this->image
                    ? url('profile_images/' . $this->image)
                    : null,
                'imageUrl' => $this->image_url,
                'createdDate' => $this->created_at?->format('F d, Y') ?? ' ',
                'createdTime' => $this->created_at?->format('h:i:s A') ?? ' ',
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? ' ',
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? ' ',
            ],
        ];
    }
}
