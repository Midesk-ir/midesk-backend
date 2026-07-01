<?php

namespace App\Http\Requests\Performance;

use App\Enums\PerformanceDataSource;
use App\Enums\PerformanceIndicatorCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformanceIndicatorRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', Rule::in(PerformanceIndicatorCategory::values())],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'measurement_unit' => ['nullable', 'string', 'max:50'],
            'target_value' => ['nullable', 'numeric', 'min:0'],
            'data_source' => ['nullable', 'string', Rule::in(PerformanceDataSource::values())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
