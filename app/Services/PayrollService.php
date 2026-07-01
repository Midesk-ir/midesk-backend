<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\PayrollRecordStatus;
use App\Enums\PersonnelStatus;
use App\Models\AttendanceRecord;
use App\Models\Contract;
use App\Models\PayrollRecord;
use App\Models\Personnel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /** @return Collection<int, PayrollRecord> */
    public function list(array $filters = []): Collection
    {
        $query = PayrollRecord::query()->with(['user', 'contract']);

        if (isset($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('period')->get();
    }

    /** @return Collection<int, PayrollRecord> */
    public function calculate(string $period): Collection
    {
        $tenant = app('tenant');
        $regionRules = config('payroll.regions.'.$tenant->region, config('payroll.regions.default'));
        $records = collect();

        $personnelList = Personnel::query()
            ->where('status', PersonnelStatus::Active)
            ->with('user')
            ->get();

        foreach ($personnelList as $personnel) {
            $contract = Contract::query()
                ->where('user_id', $personnel->user_id)
                ->where('status', ContractStatus::Active)
                ->orderByDesc('start_date')
                ->first();

            if (! $contract) {
                continue;
            }

            [$year, $month] = array_map('intval', explode('-', $period));
            $overtimeMinutes = (int) AttendanceRecord::query()
                ->where('user_id', $personnel->user_id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('overtime_minutes');

            $overtimeHours = round($overtimeMinutes / 60, 2);
            $baseSalary = (float) $contract->base_salary;
            $hourlyRate = $baseSalary / $regionRules['monthly_work_hours'];
            $overtimeAmount = round($overtimeHours * $hourlyRate * $regionRules['overtime_multiplier'], 2);

            $allowances = collect($contract->benefits ?? [])
                ->map(fn ($benefit) => is_array($benefit)
                    ? $benefit
                    : ['name' => 'benefit', 'amount' => (float) $benefit])
                ->values()
                ->all();

            $allowancesTotal = collect($allowances)->sum(fn ($a) => (float) ($a['amount'] ?? 0));
            $insurance = round($baseSalary * $regionRules['insurance_rate'], 2);
            $grossBeforeTax = $baseSalary + $overtimeAmount + $allowancesTotal;
            $tax = round($grossBeforeTax * $regionRules['tax_rate'], 2);

            $deductions = [
                ['name' => 'Insurance', 'amount' => $insurance],
                ['name' => 'Tax', 'amount' => $tax],
            ];

            $gross = $grossBeforeTax;
            $net = $gross - $insurance - $tax;

            $record = PayrollRecord::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $personnel->user_id,
                    'period' => $period,
                ],
                [
                    'contract_id' => $contract->id,
                    'base_salary' => $baseSalary,
                    'overtime_hours' => $overtimeHours,
                    'overtime_amount' => $overtimeAmount,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'gross_amount' => $gross,
                    'net_amount' => $net,
                    'currency' => $contract->currency,
                    'status' => PayrollRecordStatus::Calculated,
                    'calculated_at' => now(),
                ]
            );

            $records->push($record);
        }

        return $records;
    }

    public function approve(PayrollRecord $record): PayrollRecord
    {
        $record->update(['status' => PayrollRecordStatus::Approved]);

        return $record->fresh();
    }

    /** @param list<string> $ids */
    public function bulkPay(array $ids): Collection
    {
        return DB::transaction(function () use ($ids) {
            $records = PayrollRecord::query()
                ->whereIn('id', $ids)
                ->where('status', PayrollRecordStatus::Approved)
                ->get();

            foreach ($records as $record) {
                $record->update([
                    'status' => PayrollRecordStatus::Paid,
                    'paid_at' => now(),
                ]);
            }

            return $records;
        });
    }

    /** @return Collection<int, PayrollRecord> */
    public function payslipsForUser(string $userId): Collection
    {
        return PayrollRecord::query()
            ->where('user_id', $userId)
            ->orderByDesc('period')
            ->get();
    }
}
