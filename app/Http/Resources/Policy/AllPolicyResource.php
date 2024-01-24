<?php

namespace App\Http\Resources\Policy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPolicyResource extends JsonResource
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
            'updated_at' => (!empty($this->updated_at)) ? date('Y-m-d H:i:s', strtotime($this->updated_at)) : '',
        ];
    }
}
