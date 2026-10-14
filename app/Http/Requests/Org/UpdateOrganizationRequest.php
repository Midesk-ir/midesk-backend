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

    protected function prepareForValidation(): void
    {
        $locale = $this->input('locale');
        if (! is_string($locale)) {
            return;
        }

        $normalized = match (true) {
            str_starts_with($locale, 'fa') => 'fa',
            str_starts_with($locale, 'en') => 'en',
            default => $locale,
        };

        $this->merge(['locale' => $normalized]);
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
