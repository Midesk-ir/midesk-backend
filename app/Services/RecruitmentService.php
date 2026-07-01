<?php

namespace App\Services;

use App\Enums\CandidateStatus;
use App\Enums\EmploymentType;
use App\Enums\OnboardingProgressStatus;
use App\Enums\PersonnelStatus;
use App\Enums\RequisitionStatus;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\OnboardingProgress;
use App\Models\OnboardingTemplate;
use App\Models\Personnel;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecruitmentService
{
    public function __construct(
        private OnboardingService $onboardingService,
    ) {}

    public function listRequisitions(array $filters = []): LengthAwarePaginator
    {
        $query = Requisition::query()->with('createdBy');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate();
    }

    public function createRequisition(array $data, User $creator): Requisition
    {
        return Requisition::create([
            ...$data,
            'created_by_user_id' => $creator->id,
        ]);
    }

    public function updateRequisition(Requisition $requisition, array $data): Requisition
    {
        $requisition->update($data);

        return $requisition->fresh()->load('createdBy');
    }

    public function listCandidates(array $filters = []): LengthAwarePaginator
    {
        $query = Candidate::query()->with('requisition');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['requisition_id'])) {
            $query->where('requisition_id', $filters['requisition_id']);
        }

        return $query->orderByDesc('created_at')->paginate();
    }

    public function createCandidate(array $data): Candidate
    {
        return Candidate::create($data);
    }

    public function updateCandidate(Candidate $candidate, array $data): Candidate
    {
        $candidate->update($data);

        return $candidate->fresh()->load('requisition');
    }

    public function listInterviews(array $filters = []): LengthAwarePaginator
    {
        $query = Interview::query()->with(['candidate', 'interviewer']);

        if (! empty($filters['candidate_id'])) {
            $query->where('candidate_id', $filters['candidate_id']);
        }

        return $query->orderByDesc('scheduled_at')->paginate();
    }

    public function createInterview(array $data): Interview
    {
        return Interview::create($data);
    }

    public function updateInterview(Interview $interview, array $data): Interview
    {
        $interview->update($data);

        return $interview->fresh()->load(['candidate', 'interviewer']);
    }

    /** @return array{personnel: Personnel, onboarding: OnboardingProgress, user: User} */
    public function hire(Candidate $candidate, array $personnelData = []): array
    {
        if ($candidate->status === CandidateStatus::Hired) {
            throw ValidationException::withMessages([
                'candidate' => [__('errors.candidate_already_hired')],
            ]);
        }

        return DB::transaction(function () use ($candidate, $personnelData) {
            $user = User::create([
                'email' => $candidate->email ?? Str::uuid().'@hired.midesk.local',
                'full_name' => $candidate->full_name,
                'phone' => $candidate->phone,
                'password' => Str::random(32),
                'is_active' => true,
            ]);

            $employeeCode = $personnelData['employee_code'] ?? 'EMP-'.str_pad(
                (string) (Personnel::count() + 1),
                3,
                '0',
                STR_PAD_LEFT
            );

            $personnel = Personnel::create([
                'user_id' => $user->id,
                'employee_code' => $employeeCode,
                'hire_date' => $personnelData['hire_date'] ?? now()->toDateString(),
                'employment_type' => $personnelData['employment_type'] ?? EmploymentType::FullTime,
                'status' => PersonnelStatus::Active,
                'department' => $personnelData['department'] ?? $candidate->requisition?->department,
                'job_title' => $personnelData['job_title'] ?? $candidate->requisition?->title,
            ]);

            $onboarding = $this->onboardingService->startForUser($user);

            $candidate->update(['status' => CandidateStatus::Hired]);

            if ($candidate->requisition_id) {
                $this->updateRequisitionFillStatus($candidate->requisition_id);
            }

            return [
                'personnel' => $personnel->load('user'),
                'onboarding' => $onboarding->load('template'),
                'user' => $user,
            ];
        });
    }

    private function updateRequisitionFillStatus(string $requisitionId): void
    {
        $requisition = Requisition::findOrFail($requisitionId);
        $hiredCount = Candidate::query()
            ->where('requisition_id', $requisitionId)
            ->where('status', CandidateStatus::Hired)
            ->count();

        if ($hiredCount >= $requisition->positions_count) {
            $requisition->update(['status' => RequisitionStatus::Filled]);
        }
    }
}
