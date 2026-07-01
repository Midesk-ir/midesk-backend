<?php

namespace Database\Factories;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeaveRequest> */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $start = now()->addWeeks(2)->startOfWeek();

        return [
            'tenant_id' => null,
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $start,
            'end_date' => $start->copy()->addDays(2),
            'start_time' => null,
            'end_time' => null,
            'duration_days' => 3,
            'status' => LeaveRequestStatus::Pending,
            'note' => fake()->optional()->sentence(),
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'created_at' => now(),
        ];
    }
}
