<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PerformanceScore */
class PerformanceScoreResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'indicator_id' => $this->indicator_id,
            'period' => $this->period,
            'score' => number_format((float) $this->score, 2, '.', ''),
            'evidence' => $this->evidence,
            'evaluator_user_id' => $this->evaluator_user_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
            ]),
            'indicator' => new PerformanceIndicatorResource($this->whenLoaded('indicator')),
            'evaluator' => $this->whenLoaded('evaluator', fn () => [
                'id' => $this->evaluator->id,
                'full_name' => $this->evaluator->full_name,
            ]),
        ];
    }
}
