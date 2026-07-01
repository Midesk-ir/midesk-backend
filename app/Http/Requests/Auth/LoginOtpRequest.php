<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'phone' => ['required_without:email', 'nullable', 'string'],
            'email' => ['required_without:phone', 'nullable', 'email'],
            'code' => ['nullable', 'string', 'size:6'],
        ];
    }
}
