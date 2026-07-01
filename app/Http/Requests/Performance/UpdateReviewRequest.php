<?php

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reviewer_user_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'period' => ['sometimes', 'string', 'max:20'],
        ];
    }
}
