<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\User\AllUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = ((array) $this)['resource']->toArray();
        return [
            'id' => $this->id,
            'message' => $this->message ?? '',
            'date' => date('Y-m-d', strtotime($this->schedule_date)) ?? '',
            $this->mergeWhen((!empty($this->sender_message) && isset($resource['sender_message'])), [
                'sender_message' => (!empty($this->sender_message) && isset($resource['sender_message'])) ? new AllUserResource($this->sender_message) : '',
            ]),
        ];
    }
}
