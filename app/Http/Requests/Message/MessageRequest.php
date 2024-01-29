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
            'contact_id' => 'required_without:receiver_id|exists:contacts,id',
            'receiver_id' => 'required_without:contact_id|exists:users,id',
            'message' => 'required',
            'type' => 'required|in:image,voice,video,message',
            'gift_id' => 'nullable|exists:gifts,id',
            'date' => 'required',
            'draft' => 'nullable',
        ];
    }

    public function attributes(): array
    {
        return [
            'contact_id' => 'Contact',
            'receiver_id' => 'Receiver',
            'message' => 'Message',
            'video' => 'Video',
            'voice' => 'Voice',
            'image' => 'Image',
            'gift_id' => 'Gift',
            'date' => 'Date',
            'draft' => 'Draft',
        ];
    }
}
