<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => "required",
            'email' => "required|email|unique:users,email," . $this->route('user')->id,
            'phone' => "nullable",
            'gender' => "nullable",
            'dob' => "nullable",
            'image' => "nullable",
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => "Full name",
            'email' => "Email",
            'phone' => "Phone",
            'gender' => "Gender",
            'dob' => "Date of Birth",
            'image' => "Image",
        ];
    }
}
