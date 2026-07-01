<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'uuid', 'exists:expense_categories,id'],
            'cost_center_id' => ['sometimes', 'uuid', 'exists:cost_centers,id'],
            'budget_id' => ['nullable', 'uuid', 'exists:budgets,id'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'description' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'string'],
        ];
    }
}
