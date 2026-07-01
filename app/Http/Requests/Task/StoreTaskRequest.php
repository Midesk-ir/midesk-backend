<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'assignee_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['uuid', 'exists:labels,id'],
            'parent_task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
        ];
    }
}
