<?php

namespace App\Http\Resources\Gift;

use App\Http\Resources\User\AllUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPaymentResource extends JsonResource
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
            'id' => $this->id ?? '',
            'gift_id' => $this->gift_id ?? '',
            'amount' => $this->amount ?? '',
            'sender_name' => $this->user->full_name ?? '',
            'receiver_name' => $this->receiver->full_name ?? '',
            $this->mergeWhen((!empty($this->user) && isset($resource['user'])), [
                'sender' => (!empty($this->user) && isset($resource['user'])) ? new AllUserResource($this->user) : '',
            ]),
            $this->mergeWhen((!empty($this->receiver) && isset($resource['receiver'])), [
                'receiver' => (!empty($this->receiver) && isset($resource['receiver'])) ? new AllUserResource($this->receiver) : '',
            ]),
        ];
    }
}
