<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageSentResource extends JsonResource
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
                'chatId'      => $this->chat_id,
                'senderId'    => $this->sender_id,
                'message'     => $this->message,
                'attachment'  => $this->attachment
                    ? url('message_attachments/' . $this->attachment)
                    : null,
                'isRead'      => (bool) $this->is_read,
                'createdDate' => $this->created_at?->format('F d, Y') ?? null,
                'createdTime' => $this->created_at?->format('h:i:s A') ?? null,
                'updatedDate' => $this->updated_at?->format('F d, Y') ?? null,
                'updatedTime' => $this->updated_at?->format('h:i:s A') ?? null,
            ],

            // ✅ Include sender data using UserResource
            'sender' => $this->whenLoaded('sender', function () {
                return new UserResource($this->sender);
            }),
        ];
    }
}
