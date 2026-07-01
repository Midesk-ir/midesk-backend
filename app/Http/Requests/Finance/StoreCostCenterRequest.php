<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostCenterRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'uuid', 'exists:cost_centers,id'],
            'manager_user_id' => ['nullable', 'uuid', 'exists:users,id'],
        ];
    }
}
