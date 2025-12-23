<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemNotificationsResource extends JsonResource
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
                'senderId'      => $this->sender_id,
                'receiverId'    => $this->receiver_id,
                'barangayId'    => $this->barangay_id,

                'receiverRole'  => $this->receiver_role,
                'title'         => $this->title,
                'message'       => $this->body,

                'status'        => $this->status,
                'isRead'        => $this->status === 'read',

                'readDate'      => $this->read_at?->format('F d, Y') ?? null,
                'readTime'      => $this->read_at?->format('h:i:s A') ?? null,

                'createdDate'   => $this->created_at?->format('F d, Y') ?? null,
                'createdTime'   => $this->created_at?->format('h:i:s A') ?? null,
                'updatedDate'   => $this->updated_at?->format('F d, Y') ?? null,
                'updatedTime'   => $this->updated_at?->format('h:i:s A') ?? null,
            ],

            // Sender info (if loaded)
            'sender' => $this->whenLoaded('sender', function () {
                return [
                    'id'   => $this->sender->id,
                    'name' => $this->sender->first_name . ' ' . $this->sender->last_name,
                    'image' => $this->sender->image
                        ? url('profile_images/' . $this->sender->image)
                        : null,
                ];
            }),

            // Receiver info (if loaded)
            'receiver' => $this->whenLoaded('receiver', function () {
                return [
                    'id'   => $this->receiver->id,
                    'name' => $this->receiver->first_name . ' ' . $this->receiver->last_name,
                ];
            }),
        ];
    }
}
