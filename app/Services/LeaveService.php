<?php

namespace App\Services;

use App\Enums\LeaveRequestStatus;
use App\Jobs\GenerateLeaveBalances;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Membership;
use App\Models\User;
use App\Notifications\LeaveRequestApprovedNotification;
use App\Notifications\LeaveRequestRejectedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function createLeaveType(array $data): LeaveType
    {
        $leaveType = LeaveType::create([
            'tenant_id' => app('tenant')->id,
            ...$data,
        ]);

        GenerateLeaveBalances::dispatch($leaveType);

        return $leaveType;
    }

    public function updateLeaveType(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($data);

        GenerateLeaveBalances::dispatch($leaveType->fresh());

        return $leaveType->fresh();
    }

    public function deleteLeaveType(LeaveType $leaveType): void
    {
        if ($leaveType->requests()->whereIn('status', [
            LeaveRequestStatus::Pending,
            LeaveRequestStatus::Approved,
        ])->exists()) {
            throw ValidationException::withMessages([
                'leave_type' => [__('errors.leave_type_has_active_requests')],
            ]);
        }

        $leaveType->delete();
    }

    public function generateBalancesForLeaveType(LeaveType $leaveType, ?int $year = null): void
    {
        $year = $year ?? (int) now()->year;
        $tenantId = $leaveType->tenant_id;

        $userIds = Membership::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $balance = LeaveBalance::query()->firstOrNew([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ]);

            if (! $balance->exists) {
                $balance->total = $leaveType->default_balance_days;
                $balance->used = 0;
                $balance->remaining = $leaveType->default_balance_days;
                $balance->save();
            } else {
                $used = (float) $balance->used;
                $balance->total = $leaveType->default_balance_days;
                $balance->remaining = max(0, (float) $leaveType->default_balance_days - $used);
                $balance->save();
            }
        }
    }

    public function resetBalancesForYear(int $year): void
    {
        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $leaveType) {
            $this->generateBalancesForLeaveType($leaveType, $year);
        }
    }

    /** @return Collection<int, LeaveBalance> */
    public function balancesForUser(User $user, ?int $year = null): Collection
    {
        $year = $year ?? (int) now()->year;

        return LeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->with('leaveType')
            ->get();
    }

    public function createRequest(User $user, array $data): LeaveRequest
    {
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $duration = $this->calculateDurationDays(
            $data['start_date'],
            $data['end_date'],
            $data['start_time'] ?? null,
            $data['end_time'] ?? null,
            $leaveType
        );

        if ($leaveType->max_consecutive_days && $duration > $leaveType->max_consecutive_days) {
            throw ValidationException::withMessages([
                'duration_days' => [__('errors.leave_max_consecutive_exceeded')],
            ]);
        }

        $this->assertSufficientBalance($user, $leaveType, $duration);
        $this->assertNoOverlap($user, $data['start_date'], $data['end_date']);

        $request = LeaveRequest::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'duration_days' => $duration,
            'status' => LeaveRequestStatus::Pending,
            'note' => $data['note'] ?? null,
        ]);

        $this->notifyManagers($request, new LeaveRequestSubmittedNotification($request));

        return $request;
    }

    public function approve(LeaveRequest $request, User $reviewer): LeaveRequest
    {
        if ($request->status !== LeaveRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => [__('errors.leave_request_not_pending')],
            ]);
        }

        $this->deductBalance($request);

        $request->update([
            'status' => LeaveRequestStatus::Approved,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $request->user->notify(new LeaveRequestApprovedNotification($request));

        return $request->fresh();
    }

    public function reject(LeaveRequest $request, User $reviewer, ?string $reason = null): LeaveRequest
    {
        if ($request->status !== LeaveRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => [__('errors.leave_request_not_pending')],
            ]);
        }

        $request->update([
            'status' => LeaveRequestStatus::Rejected,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'note' => $reason ?? $request->note,
        ]);

        $request->user->notify(new LeaveRequestRejectedNotification($request));

        return $request->fresh();
    }

    public function cancel(LeaveRequest $request, User $user): LeaveRequest
    {
        if ($request->user_id !== $user->id && ! app('membership')?->isManagerOrAbove()) {
            throw ValidationException::withMessages([
                'user' => [__('errors.forbidden')],
            ]);
        }

        if ($request->status === LeaveRequestStatus::Approved) {
            $this->restoreBalance($request);
        } elseif ($request->status !== LeaveRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => [__('errors.leave_request_cannot_cancel')],
            ]);
        }

        $request->update(['status' => LeaveRequestStatus::Cancelled]);

        return $request->fresh();
    }

    /** @return Collection<int, LeaveRequest> */
    public function myRequests(User $user): Collection
    {
        return LeaveRequest::query()
            ->where('user_id', $user->id)
            ->with(['leaveType', 'reviewedBy'])
            ->orderByDesc('created_at')
            ->get();
    }

    /** @return Collection<int, LeaveRequest> */
    public function listRequests(User $viewer, bool $managerView = false): Collection
    {
        $query = LeaveRequest::query()->with(['leaveType', 'user', 'reviewedBy']);

        if (app('membership')?->isOwnerOrAdmin()) {
            return $query->orderByDesc('created_at')->get();
        }

        if ($managerView && app('membership')?->isManagerOrAbove()) {
            return $query->orderByDesc('created_at')->get();
        }

        return $query->where('user_id', $viewer->id)->orderByDesc('created_at')->get();
    }

    public function pendingCountForManager(): int
    {
        if (! app('membership')?->isManagerOrAbove()) {
            return 0;
        }

        return LeaveRequest::query()
            ->where('status', LeaveRequestStatus::Pending)
            ->count();
    }

    private function calculateDurationDays(
        string $startDate,
        string $endDate,
        ?string $startTime,
        ?string $endTime,
        LeaveType $leaveType
    ): float {
        if ($leaveType->unit->value === 'hour' && $startTime && $endTime) {
            $start = Carbon::parse("{$startDate} {$startTime}");
            $end = Carbon::parse("{$endDate} {$endTime}");

            return round($start->diffInMinutes($end) / 480, 2);
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return (float) $start->diffInDays($end) + 1;
    }

    private function assertSufficientBalance(User $user, LeaveType $leaveType, float $duration): void
    {
        $balance = LeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', (int) now()->year)
            ->first();

        if (! $balance || (float) $balance->remaining < $duration) {
            throw ValidationException::withMessages([
                'duration_days' => [__('errors.leave_insufficient_balance')],
            ]);
        }
    }

    private function assertNoOverlap(User $user, string $startDate, string $endDate): void
    {
        $overlap = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [LeaveRequestStatus::Pending, LeaveRequestStatus::Approved])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => [__('errors.leave_overlap')],
            ]);
        }
    }

    private function deductBalance(LeaveRequest $request): void
    {
        $balance = LeaveBalance::query()
            ->where('user_id', $request->user_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', (int) $request->start_date->year)
            ->firstOrFail();

        $balance->used = (float) $balance->used + (float) $request->duration_days;
        $balance->remaining = max(0, (float) $balance->total - (float) $balance->used);
        $balance->save();
    }

    private function restoreBalance(LeaveRequest $request): void
    {
        $balance = LeaveBalance::query()
            ->where('user_id', $request->user_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', (int) $request->start_date->year)
            ->first();

        if ($balance) {
            $balance->used = max(0, (float) $balance->used - (float) $request->duration_days);
            $balance->remaining = (float) $balance->total - (float) $balance->used;
            $balance->save();
        }
    }

    private function notifyManagers(LeaveRequest $request, object $notification): void
    {
        $managers = Membership::query()
            ->where('tenant_id', $request->tenant_id)
            ->whereIn('role', ['OWNER', 'ADMIN', 'MANAGER', 'HR_MANAGER'])
            ->with('user')
            ->get();

        foreach ($managers as $membership) {
            $membership->user?->notify($notification);
        }
    }
}
