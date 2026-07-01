<?php

namespace App\Http\Requests\Kanban;

use Illuminate\Foundation\Http\FormRequest;

class DeleteKanbanColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'target_column_id' => ['required', 'uuid', 'exists:kanban_columns,id'],
        ];
    }
}
