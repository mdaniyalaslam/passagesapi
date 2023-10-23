<?php

namespace App\Http\Resources\Log;

use App\Http\Resources\User\AllUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'date' => $this->date,
            'message' => $this->message,
            'user' => new AllUserResource($this->user),
        ];
    }
}
