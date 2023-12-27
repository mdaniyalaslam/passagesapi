<?php

namespace App\Http\Resources\Help;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllHelpResource extends JsonResource
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
            'title' => $this->title ?? '',
            'desc' => $this->desc ?? '',
        ];
    }
}
