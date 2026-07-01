<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'role' => ['required', Rule::enum(ProjectMemberRole::class)],
        ];
    }
}
