<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingChatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the last message of the chat
        $lastMessage = $this->messages()->latest()->first();

        return [
            'id' => $this->id,

            'attributes' => [
                'senderId'    => $lastMessage?->sender_id,
                'message'     => $lastMessage?->message,
                'attachment'  => $lastMessage && $lastMessage->attachment
                    ? url('message_attachments/' . $lastMessage->attachment)
                    : null,
                'isRead'      => (bool) $lastMessage?->is_read,
                'createdDate' => $lastMessage?->created_at?->format('F d, Y') ?? null,
                'createdTime' => $lastMessage?->created_at?->format('h:i:s A') ?? null,
                'updatedDate' => $lastMessage?->updated_at?->format('F d, Y') ?? null,
                'updatedTime' => $lastMessage?->updated_at?->format('h:i:s A') ?? null,
            ],

            // Include the sender information
            'sender' => $this->whenLoaded('sender', function () {
                return new UserResource($this->sender);
            }),

            // Include the receiver information (optional)
            'receiver' => $this->whenLoaded('receiver', function () {
                return new UserResource($this->receiver);
            }),
        ];
    }
}
