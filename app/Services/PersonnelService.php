<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Contract;
use App\Models\HrDocument;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PersonnelService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Personnel::query()->with('user');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('employee_code')->paginate();
    }

    public function create(array $data): Personnel
    {
        return Personnel::create($data);
    }

    public function show(Personnel $personnel): Personnel
    {
        return $personnel->load([
            'user',
            'contracts' => fn ($q) => $q->orderByDesc('start_date')->limit(5),
            'documents' => fn ($q) => $q->orderByDesc('created_at')->limit(5),
            'attendanceRecords' => fn ($q) => $q->orderByDesc('date')->limit(7),
        ]);
    }

    public function update(Personnel $personnel, array $data): Personnel
    {
        $personnel->update($data);

        return $personnel->fresh()->load('user');
    }

    public function delete(Personnel $personnel): void
    {
        $personnel->delete();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function contacts(string $search): Collection
    {
        return Personnel::query()
            ->with('user')
            ->where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            })
            ->limit(50)
            ->get()
            ->map(fn (Personnel $p) => [
                'id' => $p->id,
                'employee_code' => $p->employee_code,
                'full_name' => $p->user?->full_name,
                'email' => $p->user?->email,
                'phone' => $p->user?->phone,
                'department' => $p->department,
                'job_title' => $p->job_title,
            ]);
    }
}
