<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'avatar_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (isset($data['name']) && ! isset($data['full_name'])) {
            $data['full_name'] = $data['name'];
        }
        unset($data['name']);

        return $data;
    }
}
