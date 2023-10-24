<?php

namespace App\Http\Resources\Chat;

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
            $this->mergeWhen((!empty($this->sender) && isset($resource['sender'])), [
                'sender' => (!empty($this->sender) && isset($resource['sender'])) ? new AllUserResource($this->sender) : '',
            ]),
            $this->mergeWhen((!empty($this->receiver) && isset($resource['receiver'])), [
                'receiver' => (!empty($this->receiver) && isset($resource['receiver'])) ? new AllUserResource($this->receiver) : '',
            ]),
            $this->mergeWhen((!empty($this->messages) && isset($resource['messages'])), [
                'messages' => (!empty($this->messages) && isset($resource['messages'])) ? AllMessageResource::collection($this->messages) : '',
            ]),
        ];
    }
}
