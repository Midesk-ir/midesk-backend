<?php

namespace App\Http\Requests\Org;

use App\Enums\CalendarType;
use App\Enums\TenantRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
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
            'region' => ['sometimes', Rule::enum(TenantRegion::class)],
            'locale' => ['sometimes', 'string', 'in:fa,en'],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'default_currency' => ['sometimes', 'string', 'max:10'],
            'calendar_type' => ['sometimes', Rule::enum(CalendarType::class)],
        ];
    }
}
