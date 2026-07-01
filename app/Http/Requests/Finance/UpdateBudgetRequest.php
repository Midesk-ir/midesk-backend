<?php

namespace App\Http\Requests\Finance;

use App\Enums\BudgetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cost_center_id' => ['sometimes', 'uuid', 'exists:cost_centers,id'],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'quarter' => ['nullable', 'integer', 'min:1', 'max:4'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'status' => ['nullable', 'string', Rule::in(BudgetStatus::values())],
        ];
    }
}
