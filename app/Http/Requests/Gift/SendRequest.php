<?php

namespace App\Http\Requests\Gift;

use Illuminate\Foundation\Http\FormRequest;

class SendRequest extends FormRequest
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
            'token' => 'required',
            'receiver_id' => 'required|exists:users,id',
            'gift_id' => 'nullable',
            'amount' => 'required|gt:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'token' => 'Card Token',
            'receiver_id' => 'Receiver',
            'gift_id' => 'Gift',
            'amount' => 'Amount',
        ];
    }
}
