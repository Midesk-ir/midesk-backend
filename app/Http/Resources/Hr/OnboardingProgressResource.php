<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OnboardingProgress */
class OnboardingProgressResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'template_id' => $this->template_id,
            'template' => $this->whenLoaded('template', fn () => $this->template->name),
            'current_step' => $this->current_step,
            'status' => $this->status->value,
            'started_at' => $this->started_at->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'step_completions' => $this->step_completions,
        ];
    }
}
