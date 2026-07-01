<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PerformanceReviewFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    /** @use HasFactory<PerformanceReviewFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'reviewer_user_id',
        'period',
        'status',
        'self_assessment_text',
        'manager_feedback',
        'overall_score',
        'strengths',
        'improvements',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'overall_score' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
