<?php

namespace App\Http\Resources\Message;

use App\Http\Resources\User\AllUserResource;
use App\Models\Message;
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
        $user_id = auth()->user()->id;
        $query = Message::where('chat_id',$this->id)->where('is_read',0)->where('is_draft',0);
        $query->where(function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->orWhere(function ($query) use ($user_id) {
                        $query->where('receiver_id', $user_id)
                            ->where('is_schedule', true);
                    });
            });
        $messages = $query->count();
        $read = ($messages > 0) ? 0 : 1;
        $resource = ((array) $this)['resource']->toArray();
        return [
            'id' => $this->id,
            'user_id' => $this->user_id ?? '',
            'receiver_id' => $this->receiver_id ?? '',

            'date' => date('Y-m-d', strtotime($this->date)) ?? '',
            'is_read' => $read,
            'unread_message_count' => $messages ?? 0,
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
