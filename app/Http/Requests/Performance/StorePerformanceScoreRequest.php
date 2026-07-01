<?php

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'indicator_id' => ['required', 'uuid', 'exists:performance_indicators,id'],
            'period' => ['required', 'string', 'max:20'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'evidence' => ['nullable', 'string'],
        ];
    }
}
