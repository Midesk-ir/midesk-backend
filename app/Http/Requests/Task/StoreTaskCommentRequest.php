<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'mentions' => ['nullable', 'array'],
            'mentions.*' => ['uuid', 'exists:users,id'],
        ];
    }
}
