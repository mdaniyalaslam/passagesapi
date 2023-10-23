<?php

namespace App\Http\Requests\Event;

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
            'contact_id' => 'required|exists:contacts,id',
            'name' => 'required',
            'desc' => 'required',
            'date' => 'required'
        ];
    }

    public function attributes(): array
    {
        return [
            'contact_id' => 'Contact',
            'name' => 'Name',
            'desc' => 'Description',
            'date' => 'Date'
        ];
    }
}
