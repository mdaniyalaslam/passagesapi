<?php

namespace App\Http\Resources\ScreenText;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllScreenTextResource extends JsonResource
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
            'title1' => $this->title1 ?? '',
            'desc1' => $this->desc1 ?? '',
            'title2' => $this->title2 ?? '',
            'desc2' => $this->desc2 ?? '',
            'title3' => $this->title3 ?? '',
            'desc3' => $this->desc3 ?? '',
        ];
    }
}
