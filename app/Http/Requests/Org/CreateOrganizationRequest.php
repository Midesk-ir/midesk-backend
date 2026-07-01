<?php

namespace App\Http\Requests\Org;

use App\Enums\CalendarType;
use App\Enums\TenantRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrganizationRequest extends FormRequest
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
            'region' => ['nullable', Rule::enum(TenantRegion::class)],
            'locale' => ['nullable', 'string', 'in:fa,en'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'default_currency' => ['nullable', 'string', 'max:10'],
            'calendar_type' => ['nullable', Rule::enum(CalendarType::class)],
        ];
    }
}
