<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'my_tasks' => TaskResource::collection($this->resource['my_tasks']),
            'overdue_tasks' => TaskResource::collection($this->resource['overdue_tasks']),
            'active_sprint_tasks' => TaskResource::collection($this->resource['active_sprint_tasks'] ?? []),
            'active_projects' => ProjectResource::collection($this->resource['active_projects']),
            'week_worklog_minutes' => $this->resource['week_worklog_minutes'] ?? 0,
            'pending_leave_requests' => $this->resource['pending_leave_requests'] ?? 0,
            'upcoming_holidays' => HolidayResource::collection($this->resource['upcoming_holidays'] ?? []),
            'attendance_today' => $this->resource['attendance_today'] ?? null,
            'leave_balances' => LeaveBalanceResource::collection($this->resource['leave_balances'] ?? []),
            'expiring_contracts_count' => $this->when(
                array_key_exists('expiring_contracts_count', $this->resource),
                fn () => $this->resource['expiring_contracts_count']
            ),
            'new_candidates_count' => $this->when(
                array_key_exists('new_candidates_count', $this->resource),
                fn () => $this->resource['new_candidates_count']
            ),
            'active_onboarding_count' => $this->when(
                array_key_exists('active_onboarding_count', $this->resource),
                fn () => $this->resource['active_onboarding_count']
            ),
        ];
    }
}
