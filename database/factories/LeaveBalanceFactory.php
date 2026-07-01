<?php

namespace Database\Factories;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeaveBalance> */
class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $total = 20;

        return [
            'tenant_id' => null,
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => (int) now()->year,
            'total' => $total,
            'used' => 0,
            'remaining' => $total,
            'created_at' => now(),
        ];
    }
}
