<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\HrSetting;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        private HrSettingsService $hrSettings,
    ) {}

    public function checkIn(User $user): AttendanceRecord
    {
        $today = now()->toDateString();
        $record = $this->getOrCreateTodayRecord($user, $today);

        if ($record->check_in_at && $record->check_out_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_already_checked_out')],
            ]);
        }

        if ($record->check_in_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_already_checked_in')],
            ]);
        }

        $record->update([
            'check_in_at' => now(),
            'status' => $this->determineCheckInStatus($record),
        ]);

        return $record->fresh()->load('shift');
    }

    public function checkOut(User $user): AttendanceRecord
    {
        $record = $this->getTodayRecord($user);

        if (! $record?->check_in_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_not_checked_in')],
            ]);
        }

        if ($record->check_out_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_already_checked_out')],
            ]);
        }

        if ($record->break_start_at && ! $record->break_end_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_on_break')],
            ]);
        }

        $record->update(['check_out_at' => now()]);
        $this->calculateMetrics($record);

        return $record->fresh()->load('shift');
    }

    public function startBreak(User $user): AttendanceRecord
    {
        $record = $this->getTodayRecord($user);

        if (! $record?->check_in_at || $record->check_out_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_not_checked_in')],
            ]);
        }

        if ($record->break_start_at && ! $record->break_end_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_already_on_break')],
            ]);
        }

        $record->update(['break_start_at' => now()]);

        return $record->fresh()->load('shift');
    }

    public function endBreak(User $user): AttendanceRecord
    {
        $record = $this->getTodayRecord($user);

        if (! $record?->break_start_at || $record->break_end_at) {
            throw ValidationException::withMessages([
                'attendance' => [__('errors.attendance_not_on_break')],
            ]);
        }

        $record->update(['break_end_at' => now()]);

        return $record->fresh()->load('shift');
    }

    public function myRecords(User $user, ?string $from = null, ?string $to = null): LengthAwarePaginator
    {
        $query = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->with('shift')
            ->orderByDesc('date');

        if ($from) {
            $query->where('date', '>=', $from);
        }

        if ($to) {
            $query->where('date', '<=', $to);
        }

        return $query->paginate();
    }

    /** @return Collection<int, AttendanceRecord> */
    public function todayOrganization(): Collection
    {
        return AttendanceRecord::query()
            ->where('date', now()->toDateString())
            ->with(['user', 'shift'])
            ->orderBy('check_in_at')
            ->get();
    }

    public function report(?string $userId = null, ?int $month = null, ?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $month = $month ?? (int) now()->month;

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = AttendanceRecord::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $records = $query->get();

        return [
            'month' => $month,
            'year' => $year,
            'records' => $records,
            'summary' => [
                'present' => $records->where('status', AttendanceStatus::Present)->count(),
                'late' => $records->where('status', AttendanceStatus::Late)->count(),
                'absent' => $records->where('status', AttendanceStatus::Absent)->count(),
                'total_effective_minutes' => $records->sum('effective_minutes'),
                'total_overtime_minutes' => $records->sum('overtime_minutes'),
            ],
        ];
    }

    public function manualUpdate(AttendanceRecord $record, array $data): AttendanceRecord
    {
        $record->update($data);
        $this->calculateMetrics($record);

        return $record->fresh()->load(['user', 'shift']);
    }

    public function calculateMetrics(AttendanceRecord $record): void
    {
        if (! $record->check_in_at || ! $record->check_out_at) {
            return;
        }

        $checkIn = $record->check_in_at;
        $checkOut = $record->check_out_at;
        $totalMinutes = $checkIn->diffInMinutes($checkOut);

        $breakMinutes = 0;
        if ($record->break_start_at && $record->break_end_at) {
            $breakMinutes = $record->break_start_at->diffInMinutes($record->break_end_at);
        }

        $effectiveMinutes = max(0, $totalMinutes - $breakMinutes);
        $shiftDuration = $this->getShiftDurationMinutes($record->shift);
        $overtimeMinutes = max(0, $effectiveMinutes - $shiftDuration);

        $status = $this->determineStatus($record, $effectiveMinutes, $shiftDuration);

        $record->update([
            'effective_minutes' => $effectiveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $status,
        ]);
    }

    public function autoCheckout(): int
    {
        $settings = $this->hrSettings->getSettings();
        if (! ($settings['auto_checkout_enabled'] ?? true)) {
            return 0;
        }

        $count = 0;
        $records = AttendanceRecord::query()
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->where('date', '<=', now()->toDateString())
            ->with('shift')
            ->get();

        foreach ($records as $record) {
            $checkoutTime = $settings['auto_checkout_time'] ?? '23:59';
            $date = $record->date->format('Y-m-d');

            if ($record->shift) {
                $checkoutAt = Carbon::parse("{$date} {$record->shift->end_time}");
            } else {
                $checkoutAt = Carbon::parse("{$date} {$checkoutTime}");
            }

            $record->update([
                'check_out_at' => $checkoutAt,
                'auto_checkout' => true,
            ]);

            $this->calculateMetrics($record->fresh());
            $count++;
        }

        return $count;
    }

    public function calculateDailyAttendance(): int
    {
        $count = 0;
        $date = now()->subDay()->toDateString();

        $records = AttendanceRecord::query()
            ->where('date', $date)
            ->with('shift')
            ->get();

        foreach ($records as $record) {
            if (! $record->check_in_at) {
                $record->update(['status' => AttendanceStatus::Absent]);
            } else {
                $this->calculateMetrics($record);
            }
            $count++;
        }

        AttendanceRecord::query()
            ->where('date', $date)
            ->whereNull('check_in_at')
            ->update(['status' => AttendanceStatus::Absent]);

        return $count;
    }

    private function getOrCreateTodayRecord(User $user, string $date): AttendanceRecord
    {
        return AttendanceRecord::firstOrCreate(
            [
                'tenant_id' => app('tenant')->id,
                'user_id' => $user->id,
                'date' => $date,
            ],
            ['shift_id' => $this->getDefaultShift()?->id, 'status' => AttendanceStatus::Absent]
        );
    }

    private function getTodayRecord(User $user): ?AttendanceRecord
    {
        return AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereDate('date', now())
            ->first();
    }

    private function getDefaultShift(): ?Shift
    {
        return Shift::query()->where('is_default', true)->first()
            ?? Shift::query()->first();
    }

    private function determineCheckInStatus(AttendanceRecord $record): AttendanceStatus
    {
        if (! $record->shift) {
            return AttendanceStatus::Present;
        }

        $settings = $this->hrSettings->getSettings();
        $graceMinutes = $settings['attendance_grace_minutes'] ?? 15;
        $shiftStart = Carbon::parse($record->date->format('Y-m-d').' '.$record->shift->start_time);

        if (now()->gt($shiftStart->copy()->addMinutes($graceMinutes))) {
            return AttendanceStatus::Late;
        }

        return AttendanceStatus::Present;
    }

    private function determineStatus(AttendanceRecord $record, int $effectiveMinutes, int $shiftDuration): AttendanceStatus
    {
        if (! $record->check_in_at) {
            return AttendanceStatus::Absent;
        }

        if ($effectiveMinutes < ($shiftDuration / 2)) {
            return AttendanceStatus::HalfDay;
        }

        return $this->determineCheckInStatus($record);
    }

    private function getShiftDurationMinutes(?Shift $shift): int
    {
        if (! $shift) {
            return 480;
        }

        $start = Carbon::parse($shift->start_time);
        $end = Carbon::parse($shift->end_time);

        if ($end->lte($start)) {
            $end->addDay();
        }

        return (int) $start->diffInMinutes($end);
    }
}
