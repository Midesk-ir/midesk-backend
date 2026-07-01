<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequestRequest extends FormRequest
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
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.name' => ['required_with:items', 'string'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.total' => ['required_with:items', 'numeric', 'min:0'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'cost_center_id' => ['sometimes', 'uuid', 'exists:cost_centers,id'],
            'budget_id' => ['nullable', 'uuid', 'exists:budgets,id'],
            'justification' => ['nullable', 'string'],
        ];
    }
}
