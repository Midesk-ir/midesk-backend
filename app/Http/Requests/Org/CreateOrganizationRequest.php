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
            'name' => ['required', 'string', 'max:255'],
            'region' => ['nullable', Rule::enum(TenantRegion::class)],
            'locale' => ['nullable', 'string', 'in:fa,en'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'default_currency' => ['nullable', 'string', 'max:10'],
            'calendar_type' => ['nullable', Rule::enum(CalendarType::class)],
        ];
    }
}
