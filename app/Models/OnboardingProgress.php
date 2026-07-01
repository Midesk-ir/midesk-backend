<?php

namespace App\Models;

use App\Enums\OnboardingProgressStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $table = 'onboarding_progress';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'template_id',
        'current_step',
        'status',
        'started_at',
        'completed_at',
        'step_completions',
    ];

    protected function casts(): array
    {
        return [
            'status' => OnboardingProgressStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'step_completions' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<OnboardingTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingTemplate::class, 'template_id');
    }
}
