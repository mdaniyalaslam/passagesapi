<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
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
            'contact_id' => 'required|exists:contacts,id',
            'message' => 'required',
            'date' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'contact_id' => 'Contact',
            'message' => 'Message',
            'date' => 'Date',
        ];
    }
}
