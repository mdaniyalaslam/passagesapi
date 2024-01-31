<?php

namespace App\Http\Resources\Message;

use App\Http\Resources\Contact\AllContactResource;
use App\Http\Resources\Gift\AllGiftResource;
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
            'user_id' => $this->user_id ?? '',
            'receiver_id' => $this->receiver_id ?? '',
            'type' => $this->type ?? '',
            $this->mergeWhen((!empty($this->type) && $this->type != 'event'), [
                'message' => ($this->type == 'message') ? $this->message : request()->getSchemeAndHttpHost() . '/storage/' . $this->message,
            ]),
            $this->mergeWhen((!empty($this->type) && $this->type == 'event'), [
                'event_name' => $this->event_name ?? '',
                'event_desc' => $this->event_desc ?? '',
            ]),
            'date' => date('Y-m-d', strtotime($this->schedule_date)) ?? '',
            'is_read' => ($this->is_read) ? 1 : 0,
            'is_schedule' => ($this->is_schedule) ? 1 : 0,
            'is_draft' => ($this->is_draft) ? 1 : 0,
            $this->mergeWhen((!empty($this->gift) && isset($resource['gift'])), [
                'gift' => (!empty($this->gift) && isset($resource['gift'])) ? new AllGiftResource($this->gift) : '',
            ]),
            $this->mergeWhen((!empty($this->user) && isset($resource['user'])), [
                'user' => (!empty($this->user) && isset($resource['user'])) ? new AllUserResource($this->user) : '',
            ]),
            $this->mergeWhen((!empty($this->receiver) && isset($resource['receiver'])), [
                'receiver' => (!empty($this->receiver) && isset($resource['receiver'])) ? new AllUserResource($this->receiver) : '',
            ]),
        ];
    }
}
