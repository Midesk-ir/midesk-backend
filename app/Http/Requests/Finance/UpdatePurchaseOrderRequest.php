<?php

namespace App\Http\Requests\Finance;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'vendor_id' => ['sometimes', 'uuid', 'exists:vendors,id'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.name' => ['required_with:items', 'string'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.total' => ['required_with:items', 'numeric', 'min:0'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'status' => ['nullable', 'string', Rule::in(PurchaseOrderStatus::values())],
        ];
    }
}
