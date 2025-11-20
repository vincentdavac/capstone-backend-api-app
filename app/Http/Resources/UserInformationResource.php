<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserInformationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'contactNumber' => $this->contact_number,
            'houseNo' => $this->house_no,
            'street' => $this->street,
            'municipality' => $this->municipality,
            'isActive' => $this->is_active,
            'registrationStatus' => $this->registration_status,
            'userType' => $this->user_type,
           'createdDate' => $this->created_at
                ? $this->created_at->format('F d, Y')
                : null,

            'createdTime' => $this->created_at
                ? $this->created_at->format('h:i A')
                : null,

             // ✅ Uploaded files
                'image' => $this->image
                    ? url('profile_images/' . $this->image)
                    : null,
                'idDocument' => $this->id_document
                    ? url('id_documents/' . $this->id_document)
                    : null,
            'verifiedBy' => $this->verified_by,
            'dateVerified' => $this->date_verified
                ? $this->date_verified->format('F d, Y h:i A')
                : null,
            'emailVerifiedAt' => $this->email_verified_at
                ? $this->email_verified_at->format('F d, Y h:i A')
                : null,

            // Include verifier if loaded
            'verifier' => $this->whenLoaded('verifier', function () {
                return [
                    'id' => $this->verifier->id,
                    'name' => $this->verifier->first_name . ' ' . $this->verifier->last_name,
                ];
            }),

            // Include barangay and its buoys if loaded
            'barangay' => $this->whenLoaded('barangay', function () {
                return [
                    'id' => $this->barangay->id,
                    'name' => $this->barangay->name,
                    'number' => $this->barangay->number,
                    'buoys' => $this->barangay->buoys->map(function ($buoy) {
                        return [
                            'id' => $buoy->id,
                            'buoyCode' => $buoy->buoy_code,
                            'riverName' => $buoy->river_name,
                            'status' => $buoy->status,
                        ];
                    }),
                ];
            }),
        ];
    }
}
