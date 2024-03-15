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
            'is_privacy_policy' => $this->is_privacy_policy ?? '',
            'phone' => $this->phone ?? '',
            'gender' => $this->gender ?? '',
            'dob' => date('Y-m-d', strtotime($this->dob)) ?? '',
            $this->mergeWhen((!empty($this->account_type) && isset($resource['account_type'])), [
                'account_type' => (!empty($this->account_type) && isset($resource['account_type'])) ? $this->account_type : '',
            ]),
            $this->mergeWhen((!empty($this->account_id) && isset($resource['account_id'])), [
                'account_id' => (!empty($this->account_id) && isset($resource['account_id'])) ? $this->account_id : '',
            ]),
            $this->mergeWhen((!empty($this->given_name) && isset($resource['given_name'])), [
                'given_name' => (!empty($this->given_name) && isset($resource['given_name'])) ? $this->given_name : '',
            ]),
            $this->mergeWhen((!empty($this->family_name) && isset($resource['family_name'])), [
                'family_name' => (!empty($this->family_name) && isset($resource['family_name'])) ? $this->family_name : '',
            ]),
            $this->mergeWhen((!empty($this->device_token) && isset($resource['device_token'])), [
                'device_token' => (!empty($this->device_token) && isset($resource['device_token'])) ? $this->device_token : '',
            ]),
            'image' => ($this->image) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->image : '',
            'role' => new RoleResource($this->role),
        ];
    }
}
