<?php

namespace App\Http\Requests\Kanban;

use Illuminate\Foundation\Http\FormRequest;

class StoreKanbanColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:64'],
            'color' => ['nullable', 'string', 'max:20'],
            'order_index' => ['nullable', 'numeric'],
            'wip_limit' => ['nullable', 'integer', 'min:1'],
            'is_done_column' => ['nullable', 'boolean'],
        ];
    }
}
