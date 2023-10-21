<?php

namespace App\Http\Requests\ScreenText;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateRequest extends FormRequest
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
            'title1' => 'required',
            'desc1' => 'required',
            'title2' => 'required',
            'desc2' => 'required',
            'title3' => 'required',
            'desc3' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'title1' => 'Ttile 1',
            'desc1' => 'Description 1',
            'title2' => 'Ttile 2',
            'desc2' => 'Description 2',
            'title3' => 'Ttile 3',
            'desc3' => 'Description 3',
        ];
    }
}
