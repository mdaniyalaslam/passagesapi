<?php

namespace App\Http\Requests\Contact;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'image' => 'nullable|file',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'Full Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'dob' => 'Date of Birth',
            'image' => 'Image',
        ];
    }
}
