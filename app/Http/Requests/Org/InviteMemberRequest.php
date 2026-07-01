<?php

namespace App\Http\Requests\Org;

use App\Enums\MembershipRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', Rule::enum(MembershipRole::class), Rule::notIn([MembershipRole::Owner->value])],
        ];
    }
}
