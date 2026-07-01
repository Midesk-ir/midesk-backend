<?php

namespace App\Http\Requests\Kanban;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKanbanColumnRequest extends FormRequest
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
            'color' => ['nullable', 'string', 'max:20'],
            'order_index' => ['sometimes', 'numeric'],
            'wip_limit' => ['nullable', 'integer', 'min:1'],
            'is_done_column' => ['sometimes', 'boolean'],
        ];
    }
}
