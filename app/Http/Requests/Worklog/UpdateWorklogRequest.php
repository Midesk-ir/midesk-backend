<?php

namespace App\Http\Requests\Worklog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorklogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
            'project_id' => ['sometimes', 'uuid', 'exists:projects,id'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'logged_date' => ['sometimes', 'date'],
        ];
    }
}
