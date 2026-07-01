<?php

namespace App\Http\Requests\Performance;

use App\Enums\ObjectiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'period' => ['sometimes', 'string', 'max:20'],
            'status' => ['nullable', 'string', Rule::in(ObjectiveStatus::values())],
            'parent_objective_id' => ['nullable', 'uuid', 'exists:objectives,id'],
        ];
    }
}
