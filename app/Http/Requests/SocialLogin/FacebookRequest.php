<?php

namespace App\Http\Requests\SocialLogin;

use Illuminate\Foundation\Http\FormRequest;

class FacebookRequest extends FormRequest
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
            'id' => 'required',
            'token' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'Account Id',
            'token' => 'Device Token',
        ];
    }
}
