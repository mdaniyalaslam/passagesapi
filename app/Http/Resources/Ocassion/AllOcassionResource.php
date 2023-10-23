<?php

namespace App\Http\Resources\Ocassion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllOcassionResource extends JsonResource
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
            'image' => ($this->image) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->image : '',
        ];
    }
}
