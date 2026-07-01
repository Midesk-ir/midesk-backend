<?php

namespace App\Http\Requests\Sprint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseSprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'move_to' => ['required', Rule::in(['backlog', 'next_sprint_id'])],
            'next_sprint_id' => ['nullable', 'uuid', 'exists:sprints,id', 'required_if:move_to,next_sprint_id'],
        ];
    }
}
