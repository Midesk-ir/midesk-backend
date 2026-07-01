<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PerformanceReview */
class PerformanceReviewResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'reviewer_user_id' => $this->reviewer_user_id,
            'period' => $this->period,
            'status' => $this->status->value,
            'self_assessment_text' => $this->self_assessment_text,
            'manager_feedback' => $this->manager_feedback,
            'overall_score' => $this->overall_score !== null ? (float) $this->overall_score : null,
            'strengths' => $this->strengths,
            'improvements' => $this->improvements,
            'finalized_at' => $this->finalized_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer->id,
                'full_name' => $this->reviewer->full_name,
            ]),
        ];
    }
}
