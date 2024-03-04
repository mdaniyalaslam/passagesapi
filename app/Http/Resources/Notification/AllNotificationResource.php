<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? '',
            'senderName' => $this->sender->full_name ?? '',
            'avatar' => (!empty($this->sender->image)) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->sender->image : '',
            'receiverName' => $this->receiver->full_name ?? '',
            'navigation' => $this->navigation ?? '',
            'type' => $this->type ?? '',
            'message' => $this->notification ?? '',
            'messageProp' => (!empty($this->right_image)) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->right_image : '.',
            'date' => date('Y-m-d', strtotime($this->date)) ?? '',
            'is_read' => $this->is_read ?? 0,
        ];
    }
}
