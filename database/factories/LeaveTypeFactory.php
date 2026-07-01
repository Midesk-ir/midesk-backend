<?php

namespace Database\Factories;

use App\Enums\LeaveUnit;
use App\Models\LeaveType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeaveType> */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Annual Leave',
            'default_balance_days' => 26,
            'unit' => LeaveUnit::Day,
            'is_paid' => true,
            'requires_approval' => true,
        ];
    }
}
