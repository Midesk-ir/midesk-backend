<?php

namespace App\Http\Requests\Leave;

use App\Enums\LeaveUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'default_balance_days' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', Rule::enum(LeaveUnit::class)],
            'is_paid' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'max_consecutive_days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
