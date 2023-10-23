<?php

namespace App\Http\Requests\Gift;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'gift' => 'required|exists:gifts,id',
            'total' => 'required|numeric',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'gift' => 'Gift',
            'total' => 'Total',
        ];
    }
}
