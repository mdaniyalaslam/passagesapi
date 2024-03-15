<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => "required",
            'email' => "required|email|unique:users,email",
            'password' => "required|confirmed",
            'phone' => "nullable",
            'image' => "nullable",
            'is_privacy_policy' => "nullable"
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => "Full name",
            'email' => "Email",
            'password' => "Password",
            'phone' => "Phone",
            'image' => "Image",
        ];
    }
}
