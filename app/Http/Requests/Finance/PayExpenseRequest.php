<?php

namespace App\Http\Requests\Finance;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'method' => ['nullable', 'string', Rule::in(PaymentMethod::values())],
            'reference' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'string'],
        ];
    }
}
