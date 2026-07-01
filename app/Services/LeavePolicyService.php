<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeavePolicyService
{
    public function list(): LengthAwarePaginator
    {
        return LeavePolicy::query()->with('leaveType')->paginate();
    }

    public function create(array $data): LeavePolicy
    {
        return LeavePolicy::create($data);
    }

    public function update(LeavePolicy $policy, array $data): LeavePolicy
    {
        $policy->update($data);

        return $policy->fresh()->load('leaveType');
    }

    public function delete(LeavePolicy $policy): void
    {
        $policy->delete();
    }

    public function report(?string $userId = null, ?string $leaveTypeId = null, ?int $month = null, ?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $month = $month ?? (int) now()->month;

        $balanceQuery = LeaveBalance::query()->where('year', $year)->with(['user', 'leaveType']);

        if ($userId) {
            $balanceQuery->where('user_id', $userId);
        }

        if ($leaveTypeId) {
            $balanceQuery->where('leave_type_id', $leaveTypeId);
        }

        $requestQuery = LeaveRequest::query()
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->with(['user', 'leaveType']);

        if ($userId) {
            $requestQuery->where('user_id', $userId);
        }

        if ($leaveTypeId) {
            $requestQuery->where('leave_type_id', $leaveTypeId);
        }

        return [
            'month' => $month,
            'year' => $year,
            'balances' => $balanceQuery->get(),
            'requests' => $requestQuery->get(),
        ];
    }

    public function generateYearlyBalances(int $year): int
    {
        $count = 0;
        $leaveTypes = LeaveType::all();
        $users = User::query()
            ->whereHas('memberships', fn ($q) => $q->where('tenant_id', app('tenant')->id))
            ->get();

        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                $total = $leaveType->default_balance_days;

                LeaveBalance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                    ],
                    [
                        'total' => $total,
                        'used' => 0,
                        'remaining' => $total,
                    ]
                );
                $count++;
            }
        }

        return $count;
    }
}
