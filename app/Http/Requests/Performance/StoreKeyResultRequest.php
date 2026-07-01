<?php

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class StoreKeyResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'target_value' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'weight' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
