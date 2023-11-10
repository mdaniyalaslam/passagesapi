<?php

namespace App\Http\Resources\User;

use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllUserResource extends JsonResource
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
            'phone' => $this->phone ?? '',
            'gender' => $this->gender ?? '',
            'dob' => date('Y-m-d' , strtotime($this->dob)) ?? '',
            'is_active' => $this->is_active ?? '',
            'image' => ($this->image) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->image : '',
            'role' => new RoleResource($this->role),
        ];
    }
}
