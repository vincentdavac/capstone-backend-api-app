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
                'firstName'     => $this->first_name,
                'lastName'      => $this->last_name,
                'email'         => $this->email,
                'contactNumber' => $this->contact_number,
                'houseNo'       => $this->house_no,
                'street'        => $this->street,

                'barangay' => $this->whenLoaded('barangay', function () {
                return [
                    'id' => $this->barangay->id,
                    'name' => $this->barangay->name,
                    'buoys' => $this->barangay->buoys->map(function ($buoy) {
                        return [
                            'buoyCode' => $buoy->buoy_code,
                        ];
                    }),
                ];
            }),

                'municipality' => $this->municipality,
                'userType'     => $this->user_type,
                'isAdmin'      => (bool) $this->is_admin,
                'isActive'     => (bool) $this->is_active,
                'registrationStatus' => (bool) $this->registration_status,

                // ✅ Uploaded files
                'image' => $this->image
                    ? url('profile_images/' . $this->image)
                    : null,
                'idDocument' => $this->id_document
                    ? url('id_documents/' . $this->id_document)
                    : null,

                // ✅ Verification details
                'dateVerified' => $this->date_verified
                    ? $this->date_verified->format('F d, Y h:i A')
                    : null,

                'verifiedBy' => $this->whenLoaded('verifier', function () {
                    return [
                        'id'   => $this->verifier->id,
                        'name' => $this->verifier->first_name . ' ' . $this->verifier->last_name,
                        'email' => $this->verifier->email,
                    ];
                }),

                // ✅ Created and updated timestamps
                'createdDate' => $this->created_at?->format('F d, Y') ?? null,
                'createdTime' => $this->created_at?->format('h:i:s A') ?? null,
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? null,
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? null,
            ],
        ];
    }
}
