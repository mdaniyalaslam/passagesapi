<?php

namespace App\Http\Requests\Message;

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
            'video' => 'nullable',
            'voice' => 'nullable',
            'gift_id' => 'nullable|exists:gifts,id',
            'date' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'contact_id' => 'Contact',
            'message' => 'Message',
            'video' => 'Video',
            'voice' => 'Voice',
            'gift_id' => 'Gift',
            'date' => 'Date',
        ];
    }
}
