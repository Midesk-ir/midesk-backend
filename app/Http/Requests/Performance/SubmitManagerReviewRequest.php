<?php

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class SubmitManagerReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'manager_feedback' => ['nullable', 'string'],
            'overall_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'strengths' => ['nullable', 'string'],
            'improvements' => ['nullable', 'string'],
        ];
    }
}
