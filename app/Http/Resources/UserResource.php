<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->full_name ?? '',
            'email' => $this->email ?? '',
            'is_active' => $this->is_active ?? '',
            'phone' => $this->phone ?? '',
            'gender' => $this->gender ?? '',
            'dob' => date('Y-m-d' , strtotime($this->dob)) ?? '',
            'image' => ($this->image) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->image : '',
            'role' => new RoleResource($this->role),
        ];
    }
}
