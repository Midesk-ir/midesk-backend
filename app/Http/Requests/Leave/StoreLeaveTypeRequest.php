<?php

namespace App\Http\Requests\Leave;

use App\Enums\LeaveUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'default_balance_days' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', Rule::enum(LeaveUnit::class)],
            'is_paid' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'max_consecutive_days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
