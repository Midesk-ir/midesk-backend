<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid', 'exists:checklist_items,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'is_done' => ['sometimes', 'boolean'],
            'order_index' => ['sometimes', 'numeric'],
        ];
    }
}
