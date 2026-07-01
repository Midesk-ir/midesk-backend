<?php

namespace App\Http\Requests\Performance;

use App\Enums\ObjectiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'period' => ['required', 'string', 'max:20'],
            'status' => ['nullable', 'string', Rule::in(ObjectiveStatus::values())],
            'parent_objective_id' => ['nullable', 'uuid', 'exists:objectives,id'],
        ];
    }
}
