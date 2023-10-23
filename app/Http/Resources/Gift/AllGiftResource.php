<?php

namespace App\Http\Resources\Gift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllGiftResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'image' => ($this->image) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->image : '',
        ];
    }
}
