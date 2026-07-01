<?php

namespace App\Http\Requests\Finance;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'uuid', 'exists:vendors,id'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(InvoiceStatus::values())],
            'file_path' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
