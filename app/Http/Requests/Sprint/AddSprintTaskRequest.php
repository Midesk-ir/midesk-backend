<?php

namespace App\Http\Requests\Sprint;

use Illuminate\Foundation\Http\FormRequest;

class AddSprintTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'task_id' => ['required', 'uuid', 'exists:tasks,id'],
        ];
    }
}
