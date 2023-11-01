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
            'message' => $this->message ?? '',
            'video' => $this->video ?? '',
            'voice' => $this->voice ?? '',
            'event_name' => $this->event_name ?? '',
            'event_desc' => $this->event_desc ?? '',
            'date' => date('Y-m-d', strtotime($this->schedule_date)) ?? '',
            'is_read' => $this->is_read ?? '',
            'is_schedule' => $this->is_schedule ?? '',
            'is_draft' => $this->is_draft ?? '',
            $this->mergeWhen((!empty($this->gift) && isset($resource['gift'])), [
                'gift' => (!empty($this->gift) && isset($resource['gift'])) ? new AllGiftResource($this->gift) : '',
            ]),
            $this->mergeWhen((!empty($this->user) && isset($resource['user'])), [
                'user' => (!empty($this->user) && isset($resource['user'])) ? new AllUserResource($this->user) : '',
            ]),
            $this->mergeWhen((!empty($this->contact) && isset($resource['contact'])), [
                'contact' => (!empty($this->contact) && isset($resource['contact'])) ? new AllContactResource($this->contact) : '',
            ]),
        ];
    }
}
