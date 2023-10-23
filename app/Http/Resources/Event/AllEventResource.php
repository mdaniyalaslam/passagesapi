<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\Contact\AllContactResource;
use App\Http\Resources\User\AllUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEventResource extends JsonResource
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
            'name' => $this->name,
            'date' => $this->date,
            'desc' => $this->desc,
            $this->mergeWhen((!empty($this->contact) && isset($resource['contact'])), [
                'contact' => (!empty($this->contact) && isset($resource['contact'])) ? new AllContactResource($this->contact) : '',
            ]),
            $this->mergeWhen((!empty($this->user) && isset($resource['user'])), [
                'user' => (!empty($this->user) && isset($resource['user'])) ? new AllUserResource($this->user) : '',
            ]),
        ];
    }
}
