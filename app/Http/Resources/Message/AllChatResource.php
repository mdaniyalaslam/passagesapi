<?php

namespace App\Http\Resources\Message;

use App\Http\Resources\User\AllUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllChatResource extends JsonResource
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
            'user_id' => $this->user_id ?? '',
            'receiver_id' => $this->receiver_id ?? '',

            'date' => date('Y-m-d', strtotime($this->date)) ?? '',
            $this->mergeWhen((!empty($this->user) && isset($resource['user'])), [
                'user' => (!empty($this->user) && isset($resource['user'])) ? new AllUserResource($this->user) : '',
            ]),
            $this->mergeWhen((!empty($this->receiver) && isset($resource['receiver'])), [
                'receiver' => (!empty($this->receiver) && isset($resource['receiver'])) ? new AllUserResource($this->receiver) : '',
            ]),
            $this->mergeWhen((!empty($this->messages) && isset($resource['messages'])), [
                'messages' => (!empty($this->messages) && isset($resource['messages'])) ? AllMessageResource::collection($this->messages) : [],
            ]),
        ];
    }
}
