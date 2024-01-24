<?php

namespace App\Http\Resources\TermCondition;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllTermResource extends JsonResource
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
            'content' => $this->content ?? '',
            'updated_at' => (!empty($this->updated_at)) ? date('M d, Y h:i A', strtotime($this->updated_at)) : '',
        ];
    }
}
