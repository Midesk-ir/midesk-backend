<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /** @return Collection<int, PerformanceReview> */
    public function list(array $filters = []): Collection
    {
        $query = PerformanceReview::query()->with(['user', 'reviewer']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function create(array $data): PerformanceReview
    {
        return PerformanceReview::create([
            'tenant_id' => app('tenant')->id,
            'status' => ReviewStatus::Draft,
            ...$data,
        ]);
    }

    public function update(PerformanceReview $review, array $data): PerformanceReview
    {
        $review->update($data);

        return $review->fresh(['user', 'reviewer']);
    }

    public function submitSelfAssessment(PerformanceReview $review, User $user, string $text): PerformanceReview
    {
        $review->update([
            'self_assessment_text' => $text,
            'status' => ReviewStatus::SelfAssessment,
        ]);

        return $review->fresh();
    }

    public function submitManagerFeedback(PerformanceReview $review, User $manager, array $data): PerformanceReview
    {
        if (! app('membership')?->isManagerOrAbove()) {
            throw ValidationException::withMessages(['review' => [__('errors.forbidden')]]);
        }

        $review->update([
            'manager_feedback' => $data['manager_feedback'] ?? null,
            'overall_score' => $data['overall_score'] ?? null,
            'strengths' => $data['strengths'] ?? null,
            'improvements' => $data['improvements'] ?? null,
            'status' => ReviewStatus::ManagerReview,
        ]);

        return $review->fresh();
    }

    public function finalize(PerformanceReview $review): PerformanceReview
    {
        if (! app('membership')?->canManageHr()) {
            throw ValidationException::withMessages(['review' => [__('errors.forbidden')]]);
        }

        $review->update([
            'status' => ReviewStatus::Finalized,
            'finalized_at' => now(),
        ]);

        return $review->fresh();
    }
}
