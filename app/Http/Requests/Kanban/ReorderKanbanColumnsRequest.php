<?php

namespace App\Http\Requests\Kanban;

use Illuminate\Foundation\Http\FormRequest;

class ReorderKanbanColumnsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'column_ids' => ['required', 'array', 'min:1'],
            'column_ids.*' => ['uuid', 'exists:kanban_columns,id'],
        ];
    }
}
