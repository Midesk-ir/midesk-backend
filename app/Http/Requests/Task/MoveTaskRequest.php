<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kanban_column_id' => ['nullable', 'uuid', 'exists:kanban_columns,id', 'required_without:status'],
            'status' => ['nullable', Rule::enum(TaskStatus::class), 'required_without:kanban_column_id'],
            'order_index' => ['required', 'numeric'],
        ];
    }
}
