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
            'sender' => $this->sender->full_name ?? '',
            'sender_image' => (!empty($this->sender->image)) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->sender->image : '',
            'receiver' => $this->receiver->full_name ?? '',
            'navigation' => $this->navigation ?? '',
            'notification' => $this->notification ?? '',
            'right_image' => (!empty($this->right_image)) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->right_image : '',
            'date' => date('Y-m-d', strtotime($this->date)) ?? '',
            'is_read' => $this->is_read ?? 0,
        ];
    }
}
